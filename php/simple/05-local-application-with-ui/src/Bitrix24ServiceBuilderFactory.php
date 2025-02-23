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

namespace App;

use App\Events\EventDispatcherFactory;
use App\Repository\AuthRepositoryFactory;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\WrongConfigurationException;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Symfony\Component\HttpFoundation\Request;

readonly class Bitrix24ServiceBuilderFactory
{
    public static function createFromPlacementRequest(Request $request): ServiceBuilder
    {
        return ServiceBuilderFactory::createServiceBuilderFromPlacementRequest(
            $request,
            self::getApplicationProfile(),
            EventDispatcherFactory::create(),
            LoggerFactory::create(),
        );
    }

    /**
     * @throws WrongConfigurationException
     * @throws InvalidArgumentException
     */
    public static function createFromStoredToken(): ServiceBuilder
    {
        // init bitrix24 service builder auth data from saved auth token
        $logger = LoggerFactory::create();
        $authRepository = AuthRepositoryFactory::create($logger);

        return (new ServiceBuilderFactory(
            EventDispatcherFactory::create(),
            $logger,
        ))->init(
        // load app profile from /config/.env.local to $_ENV and create ApplicationProfile object
            self::getApplicationProfile(),
            // load oauth tokens and portal URL stored in /config/auth.json.local to LocalAppAuth object
            $authRepository->getAuth()->getAuthToken(),
            $authRepository->getAuth()->getDomainUrl()
        );
    }

    private static function getApplicationProfile(): ApplicationProfile
    {
        try {
            $profile = ApplicationProfile::initFromArray($_ENV);
            LoggerFactory::create()->debug('getApplicationProfile.finish');
            return $profile;
        } catch (InvalidArgumentException $invalidArgumentException) {
            LoggerFactory::create()->error(
                'getApplicationProfile.error',
                [
                    'message' => sprintf('cannot read config from $_ENV: %s', $invalidArgumentException->getMessage()),
                    'trace' => $invalidArgumentException->getTraceAsString()
                ]
            );
            throw $invalidArgumentException;
        }
    }
}