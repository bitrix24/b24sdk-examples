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

namespace App\Robots\Weather;

use App\Robots\RobotHandlerInterface;
use App\Robots\RobotHandlerMetadata;
use App\Robots\RobotInstallMetadata;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotMetadata;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotRequest;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotResponse;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use Psr\Log\LoggerInterface;

final readonly class Handler implements RobotHandlerInterface
{
    private const CODE = 'weather_robot';

    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * todo add to interface in sdk
     * @param RobotRequest $robotRequest
     * @return RobotResponse
     */
    public function handle(RobotRequest $robotRequest): RobotResponse
    {
        $this->logger->debug('Weather.RobotHandler.start', [
            'code' => $robotRequest->code,
            'properties' => $robotRequest->properties,
        ]);

        // complex logic
        sleep(20);


        $this->logger->debug('Weather.RobotHandler.finish');
        return new RobotResponse(
            $robotRequest->eventToken,
            (new Result(50, 'temperature is 50 degrees'))->toArray(),
            'log message from robot'
        );
    }

    /**
     * todo add to interface in sdk
     * @return RobotHandlerMetadata
     */
    public function getHandlerMetadata(): RobotHandlerMetadata
    {
        return new RobotHandlerMetadata(self::CODE);
    }

    /**
     * todo add to interface in sdk
     * @param string|null $handlerUrl
     * @param int|null $b24UserId
     * @return RobotInstallMetadata
     */
    public function getInstallMetadata(?string $handlerUrl, ?int $b24UserId): RobotInstallMetadata
    {
        return new RobotInstallMetadata(
            self::CODE,
            $handlerUrl,
            $b24UserId,
            true,
            [
                'en' => 'Weather robot'
            ],
            [
                'en' => 'Weather robot'
            ],
            Properties::getMetadata(),
            Result::getMetadata(),
            WorkflowDocumentType::buildForDeal(),
            [],
            false
        );
    }
}