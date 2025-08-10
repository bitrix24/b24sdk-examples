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

use App\Bitrix24ServiceBuilderFactory;
use App\Robots\RobotHandlerInterface;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotRequest;
use Bitrix24\SDK\Services\Workflows\Robot\Request\IncomingRobotRequest;
use Psr\Log\LoggerInterface;

final readonly class ActivityHandler
{
    public function __construct(
        /**
         * @var $handlers ActivityHandlerInterface[]
         */
        private iterable $handlers,
        private Bitrix24ServiceBuilderFactory $b24ServiceBuilderFactory,
        private LoggerInterface $logger
    ) {
    }

    public function handle(ActivityRequest $activityRequest): void
    {
        $this->logger->debug('ActivityHandler.start', [
            'code' => $activityRequest->code,
            'properties' => $activityRequest->properties,
        ]);

        // конфигурируем сервис билдер Б24
        $b24ServiceBuilder = $this->b24ServiceBuilderFactory::createFromStoredToken();

        $isHandlerFound = false;
        // ищем подходящий хендлер
        foreach ($this->handlers as $handler) {
            if ($handler->getHandlerMetadata()->isCanProcess($activityRequest->code)) {
                $this->logger->debug('ActivityHandler.handle.found', [
                    'code' => $activityRequest->code,
                    'handler' => get_class($handler),
                ]);

                $isHandlerFound = true;
                // todo add to rabbit MQ
                // handle activity request
                $result = $handler->handle($activityRequest);

                // return response
                $b24ServiceBuilder->getBizProcScope()->event()->send(
                    $result->eventToken,
                    $result->payload,
                    $result->logMessage,
                );

                $this->logger->debug('ActivityHandler.handle.finish');
            }
        }
        if ($isHandlerFound === false) {
            $this->logger->error('ActivityHandler.handle.activityNotFound', [
                'code' => $activityRequest->code,
            ]);
        }
    }
}