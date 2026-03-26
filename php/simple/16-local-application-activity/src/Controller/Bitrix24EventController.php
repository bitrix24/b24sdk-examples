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
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\RemoteEventsFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class Bitrix24EventController
{
    public function __construct(
        private LocalAppAuthRepositoryInterface $appAuthRepository,
        private LoggerInterface $logger
    ) {
    }

    public function process(Request $incomingRequest): Response
    {
        $this->logger->debug('Bitrix24EventController.process.start', [
            'request' => $incomingRequest->request->all(),
            'baseUrl' => $incomingRequest->getBaseUrl(),
        ]);

        try {
            // check is this request are valid bitrix24 event request?
            if (!RemoteEventsFactory::isCanProcess($incomingRequest)) {
                $this->logger->error('Bitrix24EventController.process.unknownRequest', [
                    'request' => $incomingRequest->request->all()
                ]);
                throw new InvalidArgumentException(
                    'Bitrix24EventController controller can process only install event requests from bitrix24'
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
                    $this->logger->debug('Bitrix24EventsController.process.onApplicationInstallEvent');

                    // save auth tokens and application token
                    $this->appAuthRepository->save(
                        new LocalAppAuth(
                            $b24Event->getAuth()->authToken,
                            $b24Event->getAuth()->domain,
                            $b24Event->getAuth()->application_token
                        )
                    );

                    // we receive valid data structure, but we must check is this data structure from target portal
                    $b24ServiceBuilder = Bitrix24ServiceBuilderFactory::createFromIncomingEvent($b24Event);
                    $this->logger->debug('Bitrix24EventsController.process.currentUser', [
                        'b24UserId' => $b24ServiceBuilder->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID,
                    ]);
                    break;
                case 'ONCRMCONTACTADD':
                    // add your event handler code
                    $this->logger->warning('processRemoteEvents.ONCRMCONTACTADD', [
                        'event_code' => $b24Event->getEventCode(),
                        'event_classname' => $b24Event::class,
                        'event_payload' => $b24Event->getEventPayload()
                    ]);
                    break;
                default:
                    $this->logger->warning('processRemoteEvents.unknownEvent', [
                        'event_code' => $b24Event->getEventCode(),
                        'event_classname' => $b24Event::class,
                        'event_payload' => $b24Event->getEventPayload()
                    ]);
                    break;
            }

            $response = new Response('OK', 200);
            $this->logger->debug('Bitrix24EventController.process.finish', [
                'response' => $response->getContent(),
                'statusCode' => $response->getStatusCode(),
            ]);
            return $response;
        } catch (Throwable $throwable) {
            $this->logger->error('InstallController.error', [
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
            ]);
            return new Response(sprintf('error on bitrix24 event processing: %s', $throwable->getMessage()), 500);
        }
    }
}
