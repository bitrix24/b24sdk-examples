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

namespace App\Controller;

use App\Bitrix24ServiceBuilderFactory;
use Bitrix24\SDK\Application\Local\Entity\LocalAppAuth;
use Bitrix24\SDK\Application\Local\Repository\LocalAppAuthRepositoryInterface;
use Bitrix24\SDK\Application\Requests\Events\OnApplicationInstall\OnApplicationInstall;
use Bitrix24\SDK\Application\Requests\Events\OnApplicationUninstall\OnApplicationUninstall;
use Bitrix24\SDK\Application\Requests\Placement\PlacementRequest;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Main\Common\EventHandlerMetadata;
use Bitrix24\SDK\Services\RemoteEventsFabric;
use Bitrix24\SDK\Services\RemoteEventsFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

readonly class Bitrix24EventsController
{
    public function __construct(
        private LocalAppAuthRepositoryInterface $appAuthRepository,
        private LoggerInterface $logger
    ) {
    }

    public function process(Request $incomingRequest): Response
    {
        $this->logger->debug('Bitrix24EventsController.process.start', [
            'request' => $incomingRequest->request->all(),
            'baseUrl' => $incomingRequest->getBaseUrl(),
        ]);

        try {
            // check is this request are valid bitrix24 event request?
            if (!RemoteEventsFactory::isCanProcess($incomingRequest)) {
                $this->logger->error('Bitrix24EventsController.process.unknownRequest', [
                    'request' => $incomingRequest->request->all()
                ]);
                throw new InvalidArgumentException(
                    'bitrix24 events controller can process only event requests from bitrix24'
                );
            }

            // get application_token for check event security signature
            // see https://apidocs.bitrix24.com/api-reference/events/safe-event-handlers.html
            // on first lifecycle event OnApplicationInstall application token is null and file with auth data doesn't exists
            // we save application_token and all next events will be validated security signature
            $applicationToken = $this->appAuthRepository->getApplicationToken();

            // create event object
            $b24Event = RemoteEventsFactory::init($this->logger)->createEvent($incomingRequest, $applicationToken);
            $this->logger->debug('Bitrix24EventsController.process.eventRequest', [
                'eventClassName' => $b24Event::class,
                'eventCode' => $b24Event->getEventCode(),
                'eventPayload' => $b24Event->getEventPayload(),
            ]);


            switch ($b24Event->getEventCode()) {
                case OnApplicationInstall::CODE:
                    $this->logger->debug('Bitrix24EventsController.process.onApplicationInstall');

                    // we receive valid data structure, but we must check is this data structure from target portal
                    $b24ServiceBuilder = Bitrix24ServiceBuilderFactory::createFromIncomingEvent($b24Event->getRequest());


                    // save auth tokens and application token
                    $this->appAuthRepository->save(
                        new LocalAppAuth(
                            $b24Event->getAuth()->authToken,
                            $b24Event->getAuth()->domain,
                            $b24Event->getAuth()->application_token
                        )
                    );
                    break;
                case 'OTHER_EVENT_CODE':
                    // add your event handler code
                    break;
                default:
                    self::getLog()->warning('processRemoteEvents.unknownEvent', [
                        'event_code' => $b24Event->getEventCode(),
                        'event_classname' => $b24Event::class,
                        'event_payload' => $b24Event->getEventPayload()
                    ]);
                    break;
            }


            return new Response('OK, placement request successfully processed', 200);
        } catch (Throwable $exception) {
            $this->logger->error('Bitrix24EventsController.error', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return new Response(sprintf('error on placement request processing: %s', $exception->getMessage()), 500);
        }
    }
}