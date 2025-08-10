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

namespace App\Robots;

use App\Bitrix24ServiceBuilderFactory;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotRequest;
use Psr\Log\LoggerInterface;

final readonly class RobotHandler
{
    public function __construct(
        /**
         * @var $robotHandlers RobotHandlerInterface[]
         */
        private iterable $robotHandlers,
        private Bitrix24ServiceBuilderFactory $b24ServiceBuilderFactory,
        private LoggerInterface $logger
    ) {
    }

    public function handle(RobotRequest $robotRequest): void
    {
        $this->logger->debug('RobotHandler.start', [
            'code' => $robotRequest->code,
            'properties' => $robotRequest->properties,
        ]);


        $b24ServiceBuilder = $this->b24ServiceBuilderFactory::createFromStoredToken();

        $isHandlerFound = false;

        foreach ($this->robotHandlers as $robotHandler) {
            if ($robotHandler->getHandlerMetadata()->robotCode === $robotRequest->code) {
                $this->logger->debug('RobotHandler.handle.robotFound', [
                    'code' => $robotRequest->code,
                    'handler' => get_class($robotHandler),
                ]);


                $isHandlerFound = true;

                // add to rabbit MQ


                // handle crm-robot request
//                $result = $robotHandler->handle($robotRequest);
//
//                // return response
//                $b24ServiceBuilder->getBizProcScope()->event()->send(
//                    $result->eventToken,
//                    $result->payload,
//                    $result->logMessage,
//                );
            }
        }
        if ($isHandlerFound === false) {
            $this->logger->error('RobotHandler.handle.robotNotFound', [
                'robotCode' => $robotRequest->code,
            ]);
        }
    }
}