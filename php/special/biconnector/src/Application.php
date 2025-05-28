<?php

declare(strict_types=1);

namespace App;

use Bitrix24\SDK\Application\Local\Entity\LocalAppAuth;
use Bitrix24\SDK\Application\Local\Infrastructure\Filesystem\AppAuthFileStorage;
use Bitrix24\SDK\Application\Local\Repository\LocalAppAuthRepositoryInterface;
use Bitrix24\SDK\Application\Requests\Events\OnApplicationInstall\OnApplicationInstall;
use Bitrix24\SDK\Services\RemoteEventsFactory;
use Bitrix24\SDK\Application\Requests\Placement\PlacementRequest;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;
use Bitrix24\SDK\Core\Exceptions\WrongConfigurationException;
use Bitrix24\SDK\Events\AuthTokenRenewedEvent;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Bitrix24\SDK\Services\Placement\Service\PlacementLocationCode;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Bitrix24\SDK\Core\Credentials\AuthToken;
use Throwable;

class Application
{
    private const CONFIG_FILE_NAME = '/config/.env';

    private const LOG_FILE_NAME = '/var/log/application.log';

    /**
     * Processes the installation request.
     *
     * This method handles the incoming request for installation, logs the start and finish of the process,
     * and processes the request if it is a valid placement request. It saves the admin authentication token
     * without the application token key.
     *
     * @param Request $incomingRequest The incoming request object.
     *
     * @return void
     */
    public static function processInstallation(Request $incomingRequest): Response
    {
        self::getLog()->debug('processInstallation.start', [
            'request' => $incomingRequest->request->all(),
            'baseUrl' => $incomingRequest->getBaseUrl(),
        ]);

        try {

            $b24Event = RemoteEventsFactory::init(self::getLog())->createEvent($incomingRequest, null);
            self::getLog()->debug('InstallController.process.eventRequest', [
                'eventClassName' => $b24Event::class,
                'eventCode' => $b24Event->getEventCode(),
                'eventPayload' => $b24Event->getEventPayload(),
            ]);

            if (!$b24Event instanceof OnApplicationInstall) {
                throw new InvalidArgumentException(
                    'InstallController controller can process only install events from bitrix24'
                );
            }

            // save admin auth token without application_token key
            self::getAuthRepository()->save(
                new LocalAppAuth(
                    $b24Event->getAuth()->authToken,
                    $b24Event->getAuth()->domain,
                    $b24Event->getAuth()->application_token
                )
            );

            self::getB24Service()->getMainScope()->core->call(
                'biconnector.connector.add',
                [
                    'fields' => [
                        'title' => 'SQLite Connector',
                        'logo' => 'data:image/jpeg;base64,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',
                        'description' => 'Connector with dummy password',
                        'urlCheck' => $_ENV['APP_DOMAIN'].'/?action=check',
                        'urlTableList' => $_ENV['APP_DOMAIN'].'/?action=table_list',
                        'urlTableDescription' => $_ENV['APP_DOMAIN'].'/?action=table_description',
                        'urlData' => $_ENV['APP_DOMAIN'].'/?action=data',
                        'settings' => [
                            [
                                'name' => 'Login',
                                'type' => 'STRING',
                                'code' => 'login'
                            ],
                            [
                                'name' => 'Password',
                                'type' => 'STRING',
                                'code' => 'password'
                            ]
                        ],
                        'sort' => 100
                    ]
                ]
            );


            $response = new Response('OK', 200);
            self::getLog()->debug('InstallController.process.finish', [
                'response' => $response->getContent(),
                'statusCode' => $response->getStatusCode(),
            ]);
            return $response;

        } catch (Throwable $throwable) {
            self::getLog()->error('InstallController.error', [
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
            ]);
            return new Response(sprintf('error on placement request processing: %s', $throwable->getMessage()), 500);
        }
    }

    /**
     * @throws WrongConfigurationException
     * @throws InvalidArgumentException
     */
    public static function getLog(): LoggerInterface
    {
        static $logger;

        if ($logger === null) {
            // load config
            self::loadConfigFromEnvFile();

            // check settings
            if (!array_key_exists('BITRIX24_PHP_SDK_LOG_LEVEL', $_ENV)) {
                throw new InvalidArgumentException('in $_ENV variables not found key BITRIX24_PHP_SDK_LOG_LEVEL');
            }

            // rotating
            $rotatingFileHandler = new RotatingFileHandler(dirname(__DIR__) . self::LOG_FILE_NAME, 0, (int) $_ENV['BITRIX24_PHP_SDK_LOG_LEVEL']);
            $rotatingFileHandler->setFilenameFormat('{filename}-{date}', 'Y-m-d');

            $logger = new Logger('App');
            $logger->pushHandler($rotatingFileHandler);
            $logger->pushProcessor(new MemoryUsageProcessor(true, true));
            $logger->pushProcessor(new UidProcessor());
        }

        return $logger;
    }

    /**
     * Retrieves an instance of the event dispatcher.
     *
     * @return EventDispatcherInterface The event dispatcher instance.
     */
    protected static function getEventDispatcher(): EventDispatcherInterface
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(
            AuthTokenRenewedEvent::class,
            function (AuthTokenRenewedEvent $authTokenRenewedEvent): void {
                self::onAuthTokenRenewedEventListener($authTokenRenewedEvent);
            }
        );
        return $eventDispatcher;
    }

    /**
     * Event listener for when the authentication token is renewed.
     *
     * @param AuthTokenRenewedEvent $authTokenRenewedEvent The event object containing the renewed authentication token.
     */
    protected static function onAuthTokenRenewedEventListener(AuthTokenRenewedEvent $authTokenRenewedEvent): void
    {
        self::getLog()->debug('onAuthTokenRenewedEventListener.start', [
            'expires' => $authTokenRenewedEvent->getRenewedToken()->authToken->expires
        ]);

        // save renewed auth token
        self::getAuthRepository()->saveRenewedToken(
            $authTokenRenewedEvent->getRenewedToken()
        );

        self::getLog()->debug('onAuthTokenRenewedEventListener.finish');
    }

    /**
     * @throws InvalidArgumentException
     * @throws UnknownScopeCodeException
     * @throws WrongConfigurationException
     */
    public static function getB24Service(?Request $request = null): ServiceBuilder
    {
        // init bitrix24 service builder auth data from saved auth token
        self::getLog()->debug('getB24Service.init', [
            'request' => $request ? $request->request->all() : null
        ]);

        self::getLog()->debug('getB24Service.createByStoredAuth');

        return (new ServiceBuilderFactory(
            self::getEventDispatcher(),
            self::getLog()
        ))->init(
                // load app profile from /config/.env.local to $_ENV and create ApplicationProfile object
                self::getApplicationProfile(),
                // load oauth tokens and portal URL stored in /config/auth.json.local to LocalAppAuth object
                self::getAuthRepository()->getAuth()->getAuthToken(),
                self::getAuthRepository()->getAuth()->getDomainUrl()
            );

    }

    /**
     * Retrieves the authentication repository.
     *
     * @return LocalAppAuthRepositoryInterface The authentication repository used for B24Service.
     */
    protected static function getAuthRepository(): LocalAppAuthRepositoryInterface
    {
        return new AppAuthFileStorage(
            dirname(__DIR__) . '/config/auth.json.local',
            new Filesystem(),
            self::getLog()
        );
    }

    /**
     * Get Application profile from environment variables
     *
     * By default behavioral
     *
     * @throws WrongConfigurationException
     * @throws UnknownScopeCodeException
     * @throws InvalidArgumentException
     */
    protected static function getApplicationProfile(): ApplicationProfile
    {
        self::getLog()->debug('getApplicationProfile.start');
        // You can find a list of local apps at the following URL:
        // https://YOUR-PORTAL-URL.bitrix24.com/devops/list/
        // Alternatively, navigate to Developer resources → Integrations in the left menu and 
        // select the target local application.

        // load config: application secrets, logging
        self::loadConfigFromEnvFile();

        try {
            $profile = ApplicationProfile::initFromArray($_ENV);
            self::getLog()->debug('getApplicationProfile.finish');
            return $profile;
        } catch (InvalidArgumentException $invalidArgumentException) {
            self::getLog()->error(
                'getApplicationProfile.error',
                [
                    'message' => sprintf('cannot read config from $_ENV: %s', $invalidArgumentException->getMessage()),
                    'trace' => $invalidArgumentException->getTraceAsString()
                ]
            );
            throw $invalidArgumentException;
        }
    }

    /**
     * Loads configuration from the environment file.
     *
     * @throws WrongConfigurationException if "symfony/dotenv" is not added as a Composer dependency.
     */
    private static function loadConfigFromEnvFile(): void
    {
        static $isConfigLoaded = null;
        if ($isConfigLoaded === null) {
            if (!class_exists(Dotenv::class)) {
                throw new WrongConfigurationException('You need to add "symfony/dotenv" as Composer dependencies.');
            }

            $argvInput = new ArgvInput();
            if (null !== $env = $argvInput->getParameterOption(['--env', '-e'], null, true)) {
                putenv('APP_ENV=' . $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $env);
            }

            if ($argvInput->hasParameterOption('--no-debug', true)) {
                putenv('APP_DEBUG=' . $_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '0');
            }

            (new Dotenv())->loadEnv(dirname(__DIR__) . self::CONFIG_FILE_NAME);

            $isConfigLoaded = true;
        }
    }
}