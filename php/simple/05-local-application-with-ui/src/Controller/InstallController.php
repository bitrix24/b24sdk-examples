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
use Bitrix24\SDK\Services\RemoteEventsFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class InstallController
{
    public function __construct(
        private LocalAppAuthRepositoryInterface $appAuthRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * it can be
     * - incoming event request on install.php when you install local app without UI
     *
     * @param Request $incomingRequest
     * @return Response
     * @throws \Bitrix24\SDK\Core\Exceptions\InvalidArgumentException
     * @throws \Bitrix24\SDK\Core\Exceptions\WrongSecuritySignatureException
     * @throws \JsonException
     */
    public function process(Request $incomingRequest): Response
    {
        $this->logger->debug('InstallController.start', [
            'request' => $incomingRequest->request->all(),
            'baseUrl' => $incomingRequest->getBaseUrl(),
        ]);

        if (PlacementRequest::isCanProcess($incomingRequest)) {
            $this->logger->debug('InstallController.placementRequest', [
                'request' => $incomingRequest->request->all()
            ]);
            $this->processOnInstallPlacementRequest(new PlacementRequest($incomingRequest));
            return new Response('OK', 200);
        }

        if (RemoteEventsFactory::isCanProcess($incomingRequest)) {
            $this->logger->debug('InstallController.b24EventRequest');

            // get application_token for check event security signature
            // see https://apidocs.bitrix24.com/api-reference/events/safe-event-handlers.html
            // on first lifecycle event OnApplicationInstall application token is null and file with auth data doesn't exists
            // we save application_token and all next events will be validated security signature
            $applicationToken = $this->appAuthRepository->getApplicationToken();

            $event = RemoteEventsFactory::init($this->logger)->createEvent($incomingRequest, $applicationToken);
            $this->logger->debug('InstallController.eventRequest', [
                'eventClassName' => $event::class,
                'eventCode' => $event->getEventCode(),
                'eventPayload' => $event->getEventPayload(),
            ]);

            if ($event->getEventCode() !== OnApplicationInstall::CODE) {
                throw new InvalidArgumentException(sprintf('unsupported event type %s', $event->getEventCode()));
            }
            $this->logger->debug('InstallController.Event.onApplicationInstall');
            // save auth tokens and application token
            $this->appAuthRepository->save(
                new LocalAppAuth(
                    $event->getAuth()->authToken,
                    $event->getAuth()->domain,
                    $event->getAuth()->application_token
                )
            );
            return new Response('OK', 200);
        }

        return new Response('unknown request', 400);
    }

    protected function processOnInstallPlacementRequest(PlacementRequest $placementRequest): void
    {
        $this->logger->debug('processRequest.processOnInstallPlacementRequest.start');

        $b24ServiceBuilder = Bitrix24ServiceBuilderFactory::createFromRequest($placementRequest->getRequest());

        $currentB24UserId = $b24ServiceBuilder->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;

        $eventHandlerUrl = sprintf(
            'https://%s/event-handler.php',
            $placementRequest->getRequest()->server->get('HTTP_HOST')
        );
        $this->logger->debug('processRequest.processOnInstallPlacementRequest.startBindEventHandlers', [
            'eventHandlerUrl' => $eventHandlerUrl
        ]);

        // register application lifecycle event handlers
        $b24ServiceBuilder->getMainScope()->eventManager()->bindEventHandlers(
            [
                // register event handlers for implemented in SDK events
                new EventHandlerMetadata(
                    OnApplicationInstall::CODE,
                    $eventHandlerUrl,
                    $currentB24UserId
                ),
                new EventHandlerMetadata(
                    OnApplicationUninstall::CODE,
                    $eventHandlerUrl,
                    $currentB24UserId,
                ),

            ]
        );
        $this->logger->debug('processRequest.processOnInstallPlacementRequest.finishBindEventHandlers');

        // save admin auth token without application_token key
        // they will arrive at OnApplicationInstall event
        $this->appAuthRepository->save(
            new LocalAppAuth(
                $placementRequest->getAccessToken(),
                $placementRequest->getDomainUrl(),
                null
            )
        );
        $this->logger->debug('processRequest.processOnInstallPlacementRequest.finish');
    }
}