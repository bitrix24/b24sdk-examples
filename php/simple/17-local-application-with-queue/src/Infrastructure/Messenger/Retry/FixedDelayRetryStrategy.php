<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger\Retry;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

final readonly class FixedDelayRetryStrategy implements RetryStrategyInterface
{
    /**
     * @param list<int> $waitingTimes
     */
    public function __construct(
        private array $waitingTimes,
    ) {
    }

    public function isRetryable(Envelope $message, ?\Throwable $throwable = null): bool
    {
        return RedeliveryStamp::getRetryCountFromEnvelope($message) < count($this->waitingTimes);
    }

    public function getWaitingTime(Envelope $message, ?\Throwable $throwable = null): int
    {
        $retryCount = RedeliveryStamp::getRetryCountFromEnvelope($message);

        return $this->waitingTimes[$retryCount] ?? 0;
    }
}
