<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Workflow\Activities;

use Bitrix24\SDK\Services\Workflows\Common\Auth;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentId;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use Bitrix24\SDK\Services\Workflows\Workflow\Request\IncomingWorkflowRequest;

/**
 * DTO for store activity call arguments
 */
readonly class ActivityRequest
{
    public function __construct(
        public string $workflowId,
        public string $code,
        public WorkflowDocumentId $workflowDocumentId,
        public WorkflowDocumentType $workflowDocumentType,
        public string $eventToken,
        public array $properties,
        public bool $isUseSubscription,
        public int $timeoutDuration,
        public int $timestamp,
        public Auth $auth
    ) {
    }

    /**
     * Create from an incoming workflow request
     */
    public static function initFromIncomingActivityRequest(IncomingWorkflowRequest $incomingActivityRequest): self
    {
        return new self(
            $incomingActivityRequest->workflowId,
            $incomingActivityRequest->code,
            $incomingActivityRequest->workflowDocumentId,
            $incomingActivityRequest->workflowDocumentType,
            $incomingActivityRequest->eventToken,
            $incomingActivityRequest->properties,
            $incomingActivityRequest->isUseSubscription,
            $incomingActivityRequest->timeoutDuration,
            $incomingActivityRequest->timestamp,
            $incomingActivityRequest->auth
        );
    }
}