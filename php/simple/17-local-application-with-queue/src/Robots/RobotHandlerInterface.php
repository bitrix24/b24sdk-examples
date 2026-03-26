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

use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotRequest;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotResponse;

interface RobotHandlerInterface
{
    public function handle(RobotRequest $robotRequest): RobotResponse;

    public function getHandlerMetadata(): RobotHandlerMetadata;

    public function getInstallMetadata(?string $handlerUrl, ?int $b24UserId): RobotInstallMetadata;
}