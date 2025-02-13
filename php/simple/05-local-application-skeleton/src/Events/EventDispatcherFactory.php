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

use Bitrix24\SDK\Events\AuthTokenRenewedEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventDispatcherFactory
{
    /**
     * Retrieves an instance of the event dispatcher.
     *
     * @return EventDispatcherInterface The event dispatcher instance.
     */
    public static function init(): EventDispatcherInterface
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(
            AuthTokenRenewedEvent::class,
            function (AuthTokenRenewedEvent $authTokenRenewedEvent): void {
                Bitrix24EventListener::onAuthTokenRenewedEventListener($authTokenRenewedEvent);
            }
        );
        return $eventDispatcher;
    }
}