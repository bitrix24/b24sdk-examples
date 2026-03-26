<?php

declare(strict_types=1);

namespace Tests\Message;

use App\Message\ActivityMessage;
use App\Message\ActivityMessageMapper;
use App\Workflow\Activities\ActivityRequest;
use Bitrix24\SDK\Services\Workflows\Common\Auth;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentId;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use PHPUnit\Framework\TestCase;

final class ActivityMessageMapperTest extends TestCase
{
    private ActivityMessageMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ActivityMessageMapper();
    }

    public function testToMessageMapsSdkObjectsToPlainPayload(): void
    {
        $request = $this->createActivityRequest();

        $message = $this->mapper->toMessage($request);

        self::assertInstanceOf(ActivityMessage::class, $message);
        self::assertSame('workflow-1', $message->workflowId);
        self::assertSame('activity.weather', $message->code);
        self::assertSame('event-token', $message->eventToken);
        self::assertSame(['crm', 'CCrmDocumentDeal', 'DEAL_42'], $message->workflowDocumentId);
        self::assertSame(['crm', 'CCrmDocumentDeal', 'DEAL'], $message->workflowDocumentType);
        self::assertSame(
            [
                'access_token' => 'access-token',
                'refresh_token' => 'refresh-token',
                'expires' => 1710003600,
                'client_endpoint' => 'https://example.bitrix24.ru',
                'server_endpoint' => 'https://oauth.bitrix.info/oauth/token/',
                'scope' => 'bizproc,crm',
                'status' => 'L',
                'application_token' => 'application-token',
                'expires_in' => 3600,
                'domain' => 'example.bitrix24.ru',
                'member_id' => 'member-1',
                'user_id' => 7,
            ],
            $message->auth
        );
        $this->assertContainsOnlyScalarsAndArrays($message->toArray());
    }

    public function testFromMessageRestoresActivityRequest(): void
    {
        $message = new ActivityMessage(
            'workflow-1',
            'activity.weather',
            'event-token',
            [
                'city' => 'Bishkek',
                'nested' => [
                    'count' => 3,
                    'flag' => true,
                ],
            ],
            true,
            30,
            1710000000,
            [
                'access_token' => 'access-token',
                'refresh_token' => 'refresh-token',
                'expires' => 1710003600,
                'client_endpoint' => 'example.bitrix24.ru',
                'server_endpoint' => 'https://oauth.bitrix.info/oauth/token/',
                'scope' => 'bizproc,crm',
                'status' => 'L',
                'application_token' => 'application-token',
                'expires_in' => 3600,
                'domain' => 'example.bitrix24.ru',
                'member_id' => 'member-1',
                'user_id' => 7,
            ],
            ['crm', 'CCrmDocumentDeal', 'DEAL_42'],
            ['crm', 'CCrmDocumentDeal', 'DEAL'],
        );

        $request = $this->mapper->fromMessage($message);

        self::assertInstanceOf(ActivityRequest::class, $request);
        self::assertSame('workflow-1', $request->workflowId);
        self::assertSame('activity.weather', $request->code);
        self::assertSame('event-token', $request->eventToken);
        self::assertSame(['city' => 'Bishkek', 'nested' => ['count' => 3, 'flag' => true]], $request->properties);
        self::assertTrue($request->isUseSubscription);
        self::assertSame(30, $request->timeoutDuration);
        self::assertSame(1710000000, $request->timestamp);
        self::assertSame('crm', $request->workflowDocumentId->moduleId);
        self::assertSame('CCrmDocumentDeal', $request->workflowDocumentId->entityId);
        self::assertSame('DEAL_42', $request->workflowDocumentId->targetDocumentId);
        self::assertSame('crm', $request->workflowDocumentType->moduleId);
        self::assertSame('CCrmDocumentDeal', $request->workflowDocumentType->entityId);
        self::assertSame('DEAL', $request->workflowDocumentType->targetDocumentId);
        self::assertSame('access-token', $request->auth->accessToken->accessToken);
        self::assertSame('refresh-token', $request->auth->accessToken->refreshToken);
        self::assertSame('https://example.bitrix24.ru', $request->auth->endpoints->getClientUrl());
        self::assertSame('https://oauth.bitrix.info/oauth/token/', $request->auth->endpoints->getAuthServerUrl());
        self::assertSame(['bizproc', 'crm'], $request->auth->scope->getScopeCodes());
        self::assertTrue($request->auth->applicationStatus->isLocal());
        self::assertSame('application-token', $request->auth->applicationToken);
        self::assertSame(3600, $request->auth->expiresIn);
        self::assertSame('example.bitrix24.ru', $request->auth->domain);
        self::assertSame('member-1', $request->auth->memberId);
        self::assertSame(7, $request->auth->userId);
    }

    public function testToMessageRejectsNonSerializableProperties(): void
    {
        $request = new ActivityRequest(
            'workflow-1',
            'activity.weather',
            new WorkflowDocumentId('crm', 'CCrmDocumentDeal', 'DEAL_42'),
            new WorkflowDocumentType('crm', 'CCrmDocumentDeal', 'DEAL'),
            'event-token',
            [
                'invalid' => new \stdClass(),
            ],
            false,
            30,
            1710000000,
            Auth::initFromArray($this->getAuthPayload())
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('properties.invalid');

        $this->mapper->toMessage($request);
    }

    private function createActivityRequest(): ActivityRequest
    {
        return new ActivityRequest(
            'workflow-1',
            'activity.weather',
            new WorkflowDocumentId('crm', 'CCrmDocumentDeal', 'DEAL_42'),
            new WorkflowDocumentType('crm', 'CCrmDocumentDeal', 'DEAL'),
            'event-token',
            [
                'city' => 'Bishkek',
                'nested' => [
                    'count' => 3,
                    'flag' => true,
                ],
            ],
            true,
            30,
            1710000000,
            Auth::initFromArray($this->getAuthPayload())
        );
    }

    /**
     * @return array<string, int|string>
     */
    private function getAuthPayload(): array
    {
        return [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'expires' => 1710003600,
            'client_endpoint' => 'example.bitrix24.ru',
            'server_endpoint' => 'https://oauth.bitrix.info/oauth/token/',
            'scope' => 'bizproc,crm',
            'status' => 'L',
            'application_token' => 'application-token',
            'expires_in' => 3600,
            'domain' => 'example.bitrix24.ru',
            'member_id' => 'member-1',
            'user_id' => 7,
        ];
    }

    /**
     * @param array<mixed> $payload
     */
    private function assertContainsOnlyScalarsAndArrays(array $payload): void
    {
        foreach ($payload as $value) {
            if (is_array($value)) {
                $this->assertContainsOnlyScalarsAndArrays($value);
                continue;
            }

            self::assertTrue(is_scalar($value) || $value === null);
        }
    }
}
