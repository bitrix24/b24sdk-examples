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
use Bitrix24\SDK\Application\Requests\Placement\PlacementRequest;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

readonly class PlacementController
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function process(Request $incomingRequest): Response
    {
        $this->logger->debug('PlacementController.start', [
            'request' => $incomingRequest->request->all(),
            'baseUrl' => $incomingRequest->getBaseUrl(),
        ]);

        try {
            // check is this request are valid placement request?
            if (!PlacementRequest::isCanProcess($incomingRequest)) {
                $this->logger->error('PlacementController.unknownRequest', [
                    'request' => $incomingRequest->request->all()
                ]);
                throw new InvalidArgumentException(
                    'Placement controller can process only placement requests from bitrix24'
                );
            }

            // init service builder with tokens from user who opened placement
            $placementRequest = new PlacementRequest($incomingRequest);
            $b24ServiceBuilder = Bitrix24ServiceBuilderFactory::createFromPlacementRequest($placementRequest->getRequest());
            $currentUser = $b24ServiceBuilder->getMainScope()->main()->getCurrentUserProfile()->getUserProfile();
            $userInfo = sprintf(
                'current user info:' . PHP_EOL .
                'user id – %s' . PHP_EOL .
                'user name - %s %s' . PHP_EOL .
                'is admin - %s',
                $currentUser->ID,
                $currentUser->NAME,
                $currentUser->LAST_NAME,
                $currentUser->ADMIN ? 'yes' : 'no'
            );


            // init service builder with tokens stored in file /config/auth.json.local
            $b24ServiceBuilder = Bitrix24ServiceBuilderFactory::createFromStoredToken();
            $userFromStoredToken = $b24ServiceBuilder->getMainScope()->main()->getCurrentUserProfile()->getUserProfile();
            $userInfo .= sprintf(
                PHP_EOL . '----------' . PHP_EOL .
                'user info for user from stored token:' . PHP_EOL .
                'user id – %s' . PHP_EOL .
                'user name - %s %s' . PHP_EOL .
                'is admin - %s',
                $userFromStoredToken->ID,
                $userFromStoredToken->NAME,
                $userFromStoredToken->LAST_NAME,
                $userFromStoredToken->ADMIN ? 'yes' : 'no'
            );

            $this->logger->debug('PlacementController.finish');
            return new Response(
                sprintf(
                    'main placement request successfully processed: %s',
                    $userInfo
                ), 200
            );
        } catch (Throwable $exception) {
            $this->logger->error('PlacementController.error', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return new Response(sprintf('error on placement request processing: %s', $exception->getMessage()), 500);
        }
    }
}