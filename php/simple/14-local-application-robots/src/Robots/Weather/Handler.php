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

use App\Robots\RobotHandlerMetadata;
use App\Robots\RobotInstallMetadata;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotHandlerInterface;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotMetadata;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotRequest;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotResponse;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use Psr\Log\LoggerInterface;

final readonly class Handler
{
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
        return new RobotResponse(
            'event_token_payload',
            (new Result(1, 'temperature is 1 degree'))->toArray(),
            'log message'
        );
    }

    /**
     * todo add to interface in sdk
     * @return RobotHandlerMetadata
     */
    public function getHandlerMetadata(): RobotHandlerMetadata
    {
        return new RobotHandlerMetadata(
            'weather_robot',
            self::class,
        );
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
            'weather_robot',
            $handlerUrl,
            $b24UserId,
            true,
            [
                'ru' => 'Робот для прогноза погоды'
            ],
            [
                'ru' => 'Робот для прогноза погоды на текущий день'
            ],
            Properties::getMetadata(),
            Result::getMetadata(),
            WorkflowDocumentType::buildForDeal(),
            [],
            false
        );
    }
}