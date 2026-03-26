<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Bitrix24ServiceBuilderFactory;
use App\Message\ActivityMessage;
use App\Message\ActivityMessageMapper;
use App\Workflow\Activities\ActivityHandler;
use Psr\Log\LoggerInterface;

final readonly class ActivityMessageHandler
{
    public function __construct(
        private ActivityMessageMapper $activityMessageMapper,
        private ActivityHandler $activityHandler,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ActivityMessage $activityMessage): void
    {
        $this->logger->debug('ActivityMessageHandler.start', [
            'workflowId' => $activityMessage->workflowId,
            'code' => $activityMessage->code,
            'eventToken' => $activityMessage->eventToken,
        ]);

        $activityRequest = $this->activityMessageMapper->fromMessage($activityMessage);
        $activityResponse = $this->activityHandler->handle($activityRequest);

        $this->logger->debug('ActivityMessageHandler.activityCompleted', [
            'workflowId' => $activityRequest->workflowId,
            'code' => $activityRequest->code,
            'eventToken' => $activityResponse->eventToken,
        ]);

        Bitrix24ServiceBuilderFactory::createFromWorkflowAuth($activityMessage->auth)
            ->getBizProcScope()
            ->event()
            ->send(
                $activityResponse->eventToken,
                $activityResponse->payload,
                $activityResponse->logMessage,
            );

        $this->logger->debug('ActivityMessageHandler.finish', [
            'workflowId' => $activityRequest->workflowId,
            'code' => $activityRequest->code,
            'eventToken' => $activityResponse->eventToken,
        ]);
    }
}
