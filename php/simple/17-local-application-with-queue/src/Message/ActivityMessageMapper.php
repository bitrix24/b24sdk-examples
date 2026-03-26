<?php

declare(strict_types=1);

namespace App\Message;

use App\Workflow\Activities\ActivityRequest;
use Bitrix24\SDK\Services\Workflows\Common\Auth;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentId;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;

final class ActivityMessageMapper
{
    public function toMessage(ActivityRequest $activityRequest): ActivityMessage
    {
        return new ActivityMessage(
            $activityRequest->workflowId,
            $activityRequest->code,
            $activityRequest->eventToken,
            $this->assertSerializableArray($activityRequest->properties, 'properties'),
            $activityRequest->isUseSubscription,
            $activityRequest->timeoutDuration,
            $activityRequest->timestamp,
            $this->mapAuthToArray($activityRequest->auth),
            $this->mapWorkflowDocumentIdToArray($activityRequest->workflowDocumentId),
            $this->mapWorkflowDocumentTypeToArray($activityRequest->workflowDocumentType),
        );
    }

    public function fromMessage(ActivityMessage $activityMessage): ActivityRequest
    {
        $authPayload = $this->assertSerializableArray($activityMessage->auth, 'auth');
        $documentIdPayload = $this->assertStringList($activityMessage->workflowDocumentId, 'workflowDocumentId');
        $documentTypePayload = $this->assertStringList($activityMessage->workflowDocumentType, 'workflowDocumentType');

        return new ActivityRequest(
            $activityMessage->workflowId,
            $activityMessage->code,
            WorkflowDocumentId::initFromArray($documentIdPayload),
            WorkflowDocumentType::initFromArray($documentTypePayload),
            $activityMessage->eventToken,
            $this->assertSerializableArray($activityMessage->properties, 'properties'),
            $activityMessage->isUseSubscription,
            $activityMessage->timeoutDuration,
            $activityMessage->timestamp,
            Auth::initFromArray($authPayload),
        );
    }

    /**
     * @return array{
     *   access_token: string,
     *   refresh_token: string|null,
     *   expires: int,
     *   client_endpoint: string,
     *   server_endpoint: string,
     *   scope: string,
     *   status: string,
     *   application_token: string,
     *   expires_in: int,
     *   domain: string,
     *   member_id: string,
     *   user_id: int
     * }
     */
    public function mapAuthToArray(Auth $auth): array
    {
        return [
            'access_token' => $auth->accessToken->accessToken,
            'refresh_token' => $auth->accessToken->refreshToken,
            'expires' => $auth->accessToken->expires,
            'client_endpoint' => $auth->endpoints->getClientUrl(),
            'server_endpoint' => $auth->endpoints->getAuthServerUrl(),
            'scope' => implode(',', $auth->scope->getScopeCodes()),
            'status' => $this->mapApplicationStatusToShortCode($auth),
            'application_token' => $auth->applicationToken,
            'expires_in' => $auth->expiresIn,
            'domain' => $auth->domain,
            'member_id' => $auth->memberId,
            'user_id' => $auth->userId,
        ];
    }

    /**
     * @return list<string>
     */
    public function mapWorkflowDocumentIdToArray(WorkflowDocumentId $workflowDocumentId): array
    {
        return [
            $workflowDocumentId->moduleId,
            $workflowDocumentId->entityId,
            $workflowDocumentId->targetDocumentId,
        ];
    }

    /**
     * @return list<string>
     */
    public function mapWorkflowDocumentTypeToArray(WorkflowDocumentType $workflowDocumentType): array
    {
        return $workflowDocumentType->toArray();
    }

    private function mapApplicationStatusToShortCode(Auth $auth): string
    {
        return match (true) {
            $auth->applicationStatus->isFree() => 'F',
            $auth->applicationStatus->isDemo() => 'D',
            $auth->applicationStatus->isTrial() => 'T',
            $auth->applicationStatus->isPaid() => 'P',
            $auth->applicationStatus->isLocal() => 'L',
            $auth->applicationStatus->isSubscription() => 'S',
            default => throw new \LogicException('Unsupported Bitrix24 application status value.'),
        };
    }

    /**
     * @return array<mixed>
     */
    private function assertSerializableArray(array $payload, string $field): array
    {
        foreach ($payload as $key => $value) {
            $this->assertSerializableValue($value, sprintf('%s.%s', $field, (string) $key));
        }

        return $payload;
    }

    /**
     * @param array<mixed> $payload
     * @return list<string>
     */
    private function assertStringList(array $payload, string $field): array
    {
        if (count($payload) !== 3) {
            throw new \InvalidArgumentException(sprintf('Field "%s" must contain exactly 3 scalar items.', $field));
        }

        $result = [];
        foreach (array_values($payload) as $index => $value) {
            if (!is_scalar($value) && $value !== null) {
                throw new \InvalidArgumentException(sprintf('Field "%s.%d" must be scalar.', $field, $index));
            }

            $result[] = (string) $value;
        }

        return $result;
    }

    private function assertSerializableValue(mixed $value, string $path): void
    {
        if (is_array($value)) {
            foreach ($value as $nestedKey => $nestedValue) {
                $this->assertSerializableValue($nestedValue, sprintf('%s.%s', $path, (string) $nestedKey));
            }

            return;
        }

        if (is_scalar($value) || $value === null) {
            return;
        }

        throw new \InvalidArgumentException(sprintf('Field "%s" must contain only scalar values or nested arrays.', $path));
    }
}
