<?php

declare(strict_types=1);

namespace App\Message;

final readonly class ActivityMessage
{
    public function __construct(
        public string $workflowId,
        public string $code,
        public string $eventToken,
        public array $properties,
        public bool $isUseSubscription,
        public int $timeoutDuration,
        public int $timestamp,
        public array $auth,
        public array $workflowDocumentId,
        public array $workflowDocumentType,
    ) {
    }

    public function toArray(): array
    {
        return [
            'workflowId' => $this->workflowId,
            'code' => $this->code,
            'eventToken' => $this->eventToken,
            'properties' => $this->properties,
            'isUseSubscription' => $this->isUseSubscription,
            'timeoutDuration' => $this->timeoutDuration,
            'timestamp' => $this->timestamp,
            'auth' => $this->auth,
            'workflowDocumentId' => $this->workflowDocumentId,
            'workflowDocumentType' => $this->workflowDocumentType,
        ];
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            (string) ($payload['workflowId'] ?? ''),
            (string) ($payload['code'] ?? ''),
            (string) ($payload['eventToken'] ?? ''),
            self::ensureArray($payload['properties'] ?? []),
            (bool) ($payload['isUseSubscription'] ?? false),
            (int) ($payload['timeoutDuration'] ?? 0),
            (int) ($payload['timestamp'] ?? 0),
            self::ensureArray($payload['auth'] ?? []),
            self::ensureArray($payload['workflowDocumentId'] ?? []),
            self::ensureArray($payload['workflowDocumentType'] ?? []),
        );
    }

    private static function ensureArray(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return $value;
    }
}
