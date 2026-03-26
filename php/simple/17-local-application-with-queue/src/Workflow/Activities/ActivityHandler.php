<?php

/**
 * This file is part of the b24sdk examples package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Workflow\Activities;

use Psr\Log\LoggerInterface;

final readonly class ActivityHandler
{
    public function __construct(
        /**
         * @var $handlers ActivityHandlerInterface[]
         */
        private iterable $handlers,
        private LoggerInterface $logger
    ) {
    }

    public function handle(ActivityRequest $activityRequest): ActivityResponse
    {
        $this->logger->debug('ActivityHandler.start', [
            'workflowId' => $activityRequest->workflowId,
            'code' => $activityRequest->code,
            'eventToken' => $activityRequest->eventToken,
            'properties' => $activityRequest->properties,
        ]);

        foreach ($this->handlers as $handler) {
            if ($handler->getHandlerMetadata()->isCanProcess($activityRequest->code)) {
                $this->logger->debug('ActivityHandler.handle.found', [
                    'workflowId' => $activityRequest->workflowId,
                    'code' => $activityRequest->code,
                    'handler' => get_class($handler),
                ]);

                $result = $handler->handle($activityRequest);

                $this->logger->debug('ActivityHandler.handle.finish', [
                    'workflowId' => $activityRequest->workflowId,
                    'code' => $activityRequest->code,
                    'eventToken' => $result->eventToken,
                ]);

                return $result;
            }
        }

        $this->logger->error('ActivityHandler.handle.activityNotFound', [
            'workflowId' => $activityRequest->workflowId,
            'code' => $activityRequest->code,
            'eventToken' => $activityRequest->eventToken,
        ]);

        throw new ActivityHandlerNotFoundException($activityRequest->code);
    }
}
