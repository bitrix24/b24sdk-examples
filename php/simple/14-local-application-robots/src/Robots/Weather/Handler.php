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

use App\Robots\RobotInstallMetadata;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotHandlerInterface;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotMetadata;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotRequest;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotResponse;
use Psr\Log\LoggerInterface;

final readonly class Handler
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * todo interface
     * @param RobotRequest $robotRequest
     * @return RobotResponse
     */
    public function handle(RobotRequest $robotRequest): RobotResponse
    {
        return new RobotResponse('event_token_payload', ['a' => 1, 'b' => 2], 'log message');
    }

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

        );
    }
}