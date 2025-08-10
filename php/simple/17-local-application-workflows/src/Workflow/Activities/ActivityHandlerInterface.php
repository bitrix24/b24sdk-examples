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

interface ActivityHandlerInterface
{
    public function handle(ActivityRequest $activityActivityRequest): ActivityResponse;

    public function getHandlerMetadata(): ActivityHandlerMetadata;

    public function getInstallMetadata(?string $handlerUrl, ?int $b24UserId): ActivityInstallMetadata;
}