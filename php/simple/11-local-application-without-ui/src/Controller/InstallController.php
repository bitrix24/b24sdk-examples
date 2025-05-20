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

use Bitrix24\SDK\Application\Local\Entity\LocalAppAuth;
use Bitrix24\SDK\Application\Local\Repository\LocalAppAuthRepositoryInterface;
use Bitrix24\SDK\Application\Requests\Events\OnApplicationInstall\OnApplicationInstall;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\RemoteEventsFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class InstallController
{
    public function __construct(
        private LocalAppAuthRepositoryInterface $appAuthRepository,
        private LoggerInterface $logger
    ) {
    }

    public function process(Request $incomingRequest): Response
    {
        $this->logger->debug('InstallController.process.start', [
            'request' => $incomingRequest->request->all(),
            'baseUrl' => $incomingRequest->getBaseUrl(),
        ]);

        try {
            // check is this request are valid bitrix24 event request?
            if (!RemoteEventsFactory::isCanProcess($incomingRequest)) {
                $this->logger->error('InstallController.process.unknownRequest', [
                    'request' => $incomingRequest->request->all()
                ]);
                throw new InvalidArgumentException(
                    'InstallController controller can process only install event requests from bitrix24'
                );
            }

            $b24Event = RemoteEventsFactory::init($this->logger)->createEvent($incomingRequest, null);
            $this->logger->debug('InstallController.process.eventRequest', [
                'eventClassName' => $b24Event::class,
                'eventCode' => $b24Event->getEventCode(),
                'eventPayload' => $b24Event->getEventPayload(),
            ]);

            if (!$b24Event instanceof OnApplicationInstall) {
                throw new InvalidArgumentException(
                    'InstallController controller can process only install events from bitrix24'
                );
            }

            // save auth tokens and application token
            $this->appAuthRepository->save(
                new LocalAppAuth(
                    $b24Event->getAuth()->authToken,
                    $b24Event->getAuth()->domain,
                    $b24Event->getAuth()->application_token
                )
            );

            $response = new Response('OK', 200);
            $this->logger->debug('InstallController.process.finish', [
                'response' => $response->getContent(),
                'statusCode' => $response->getStatusCode(),
            ]);
            return $response;
        } catch (Throwable $exception) {
            $this->logger->error('InstallController.error', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return new Response(sprintf('error on placement request processing: %s', $exception->getMessage()), 500);
        }
    }
}