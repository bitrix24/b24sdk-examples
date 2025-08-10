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

namespace App\Robots\Discount;
use App\Robots\RobotHandlerInterface;
use App\Robots\RobotHandlerMetadata;
use App\Robots\RobotInstallMetadata;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotRequest;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotResponse;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;

class Handler implements RobotHandlerInterface
{
    private const CODE = 'discount_robot';
    public function handle(RobotRequest $robotRequest): RobotResponse
    {
        sleep(20);


        return new RobotResponse(
            'event_token_payload',
            (new Result('20', '20 rub discount'))->toArray(),
            'log message from robot discount'
        );
    }

    public function getHandlerMetadata(): RobotHandlerMetadata
    {
        return new RobotHandlerMetadata(self::CODE);
    }

    public function getInstallMetadata(?string $handlerUrl, ?int $b24UserId): RobotInstallMetadata
    {
        return new RobotInstallMetadata(
            self::CODE,
            $handlerUrl,
            $b24UserId,
            true,
            [
                'ru' => 'Робот для скидок'
            ],
            [
                'ru' => 'Робот для скидок на сделки'
            ],
            Properties::getMetadata(),
            Result::getMetadata(),
            WorkflowDocumentType::buildForDeal(),
            [],
            false
        );
    }
}