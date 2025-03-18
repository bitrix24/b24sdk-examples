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
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\WrongConfigurationException;
use Bitrix24\SDK\Events\AuthTokenRenewedEvent;

readonly class Bitrix24EventListener
{
    /**
     * @throws WrongConfigurationException
     * @throws InvalidArgumentException
     */
    public static function onAuthTokenRenewedEventListener(AuthTokenRenewedEvent $authTokenRenewedEvent): void
    {
        // save renewed auth token
        AuthRepositoryFactory::create(LoggerFactory::create())->saveRenewedToken($authTokenRenewedEvent->getRenewedToken());
    }
}