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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

readonly class InstallController
{
    public function __construct(
        private LocalAppAuthRepositoryInterface $appAuthRepository,
        private LoggerInterface $logger
    ) {
    }

    public function process(Request $incomingRequest): Response
    {
        $this->logger->debug('InstallController.start', [
            'request' => $incomingRequest->request->all(),
            'baseUrl' => $incomingRequest->getBaseUrl(),
        ]);

        try {
            // check is this request are valid placement request?
            if (!PlacementRequest::isCanProcess($incomingRequest)) {
                $this->logger->error('InstallController.unknownRequest', [
                    'request' => $incomingRequest->request->all()
                ]);
                throw new InvalidArgumentException(
                    'install controller can process only placement requests from bitrix24'
                );
            }

            $placementRequest = new PlacementRequest($incomingRequest);
            $b24ServiceBuilder = Bitrix24ServiceBuilderFactory::createFromPlacementRequest(
                $placementRequest->getRequest()
            );

            // your code can't trust data in request before you check is this request data valid
            $b24ServiceBuilder->getMainScope()->main()->guardValidateCurrentAuthToken();

            // ok, request data is valid, let's install application
            $currentB24UserId = $b24ServiceBuilder->getMainScope()->main()->getCurrentUserProfile()->getUserProfile(
            )->ID;

            $eventHandlerUrl = sprintf(
                'https://%s/event-handler.php',
                $placementRequest->getRequest()->server->get('HTTP_HOST')
            );
            $this->logger->debug('processOnInstallPlacementRequest.startBindEventHandlers', [
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
            $this->logger->debug('processOnInstallPlacementRequest.finishBindEventHandlers');

            // save admin auth token without application_token key
            // they will arrive at OnApplicationInstall event
            $this->appAuthRepository->save(
                new LocalAppAuth(
                    $placementRequest->getAccessToken(),
                    $placementRequest->getDomainUrl(),
                    null
                )
            );

            return new Response('OK, placement request successfully processed', 200);
        } catch (Throwable $exception) {
            $this->logger->error('InstallController.error', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return new Response(sprintf('error on placement request processing: %s', $exception->getMessage()), 500);
        }
    }
}