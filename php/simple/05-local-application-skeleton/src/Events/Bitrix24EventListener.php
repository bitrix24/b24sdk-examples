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

namespace App\Events;

use App\LoggerFactory;
use App\Repository\AuthRepositoryFactory;
use Bitrix24\SDK\Events\AuthTokenRenewedEvent;

class Bitrix24EventListener
{
    public static function onAuthTokenRenewedEventListener(AuthTokenRenewedEvent $authTokenRenewedEvent): void
    {
        // save renewed auth token
        AuthRepositoryFactory::init(LoggerFactory::getLog())->saveRenewedToken(
            $authTokenRenewedEvent->getRenewedToken()
        );
    }
}