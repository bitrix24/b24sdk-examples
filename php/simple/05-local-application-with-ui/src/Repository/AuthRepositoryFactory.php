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

namespace App\Repository;

use Bitrix24\SDK\Application\Local\Infrastructure\Filesystem\AppAuthFileStorage;
use Bitrix24\SDK\Application\Local\Repository\LocalAppAuthRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

readonly class AuthRepositoryFactory
{
    public static function create(LoggerInterface $logger): LocalAppAuthRepositoryInterface
    {
        return new AppAuthFileStorage(
            dirname(__DIR__,2) . '/config/auth.json.local',
            new Filesystem(),
            $logger
        );
    }
}