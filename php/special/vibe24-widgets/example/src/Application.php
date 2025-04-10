<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App;

use Bitrix24\SDK\Application\Local\Entity\LocalAppAuth;
use Bitrix24\SDK\Application\Local\Infrastructure\Filesystem\AppAuthFileStorage;
use Bitrix24\SDK\Application\Local\Repository\LocalAppAuthRepositoryInterface;
use Bitrix24\SDK\Application\Requests\Placement\PlacementRequest;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;
use Bitrix24\SDK\Core\Exceptions\WrongConfigurationException;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Events\AuthTokenRenewedEvent;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
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
use App\Tasks;

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
    public static function processInstallation(Request $incomingRequest)
    {
        self::getLog()->debug('processInstallation.start', [
            'request' => $incomingRequest->request->all(),
            'baseUrl' => $incomingRequest->getBaseUrl(),
        ]);

        if (PlacementRequest::isCanProcess($incomingRequest)) {

            self::getLog()->debug('processInstallation.placementRequest', [
                'request' => $incomingRequest->request->all()
            ]);
            
            $placementRequest = new PlacementRequest($incomingRequest);

            // save admin auth token without application_token key
            self::getAuthRepository()->save(
                new LocalAppAuth(
                    $placementRequest->getAccessToken(),
                    $placementRequest->getDomainUrl(),
                    null
                )
            );

        }

        self::getLog()->debug('processInstallation.finish');
    }

    /**
     * Creates and registers widgets from the specified directory.
     *
     * This method scans the given directory for widget folders, reads their settings,
     * and registers them using the Bitrix24 API.
     *
     * @param string $widgetsDir The directory containing widget folders. Defaults to 'widgets'.
     *
     * @throws BaseException If the specified directory does not exist.
     *
     * The method performs the following steps:
     * 1. Logs the start of the widget creation process.
     * 2. Checks if the specified directory exists.
     * 3. Scans the directory for widget folders.
     * 4. For each widget folder:
     *    - Reads the widget settings from 'settings.json'.
     *    - Sets the preview image, style, and handler URLs.
     *    - Reads demo data from 'demo.json' if available.
     *    - Reads the widget content from 'template.html' if available.
     *    - Registers the widget using the Bitrix24 API.
     *    - Logs the registration details.
     * 5. Logs the completion of the widget creation process.
     */
    public static function createWidgets (string $widgetsDir = 'widgets') {

        self::getLog()->debug('createWidgets.start', [
            'widgetsDir' => $widgetsDir
        ]);

        if (!is_dir($widgetsDir)) {
            throw new BaseException("Directory with widgets not found");
        }

        $domain = "https://" . $_SERVER['HTTP_HOST'];

        $widgetFolders = scandir($widgetsDir);

        foreach ($widgetFolders as $folder) {
 
            if ($folder === '.' || $folder === '..') {
                continue;
            }

            $widgetPath = $widgetsDir . DIRECTORY_SEPARATOR . $folder;

            if (is_dir($widgetPath)) {

                $params = [
                    'code' => 'my-super-widget-'.$folder,
                ];

                $settingsFile = $widgetPath . DIRECTORY_SEPARATOR . 'settings.json';
                if (file_exists($settingsFile)) {
                    $params['fields'] = json_decode(file_get_contents($settingsFile), true);
                }

                $params['fields']['PREVIEW'] = $domain . '/' . $widgetPath . '/preview.jpg';
                $params['fields']['WIDGET_PARAMS']['style'] = $domain . '/' . $widgetPath . '/template.css';
                $params['fields']['WIDGET_PARAMS']['handler'] = $domain . '/widget-handler.php?widget='.$params['code'];

                $demoFile = $widgetPath . DIRECTORY_SEPARATOR . 'demo.json';
                if (file_exists($demoFile)) {
                    $params['fields']['WIDGET_PARAMS']['demoData'] = json_decode(file_get_contents($demoFile), true);
                }

                $contentFile = $widgetPath . DIRECTORY_SEPARATOR . 'template.html';
                if (file_exists($contentFile)) {
                    $params['fields']['CONTENT'] = file_get_contents($contentFile);
                }

                $result = self::getB24Service()
                    ->core->call(
                        'landing.repowidget.register',
                        $params
                    )->getResponseData()->getResult();

                self::getLog()->debug('createWidgets.registration', [
                    'params' => $params,
                    'result' => $result
                ]);

            }
        }

        self::getLog()->debug('createWidgets.finish');
    }

    /**
     * Processes the widget request and returns a JSON response.
     *
     * @param Request $incomingRequest The incoming HTTP request.
     * @param string|null $widgetCode The code of the widget being requested.
     * 
     * @return Response The HTTP response containing the processed widget data or an error message.
     *
     * The function handles the widget request based on the provided widget code. If the widget code
     * is 'my-super-widget-problem-tasks', it processes the request to fetch tasks with pagination,
     * filtering, and sorting options. The response includes the current page, sorting details, total
     * pages, task items, and user information. If the widget code is unknown, it returns an error response.
     */
    public static function processWidgetRequest (Request $incomingRequest, string $widgetCode = null): Response {
        
        if ($widgetCode == 'my-super-widget-problem-tasks') {

            $pageIndex = $incomingRequest->request->getInt('pageIndex', 1);
            $filter = $incomingRequest->request->get('filter', '');
            $newSortBy = $incomingRequest->request->get('newSortBy', 'id');
            $curSortBy = $incomingRequest->request->get('curSortBy', '');
            $curSortOrder = filter_var(
                $incomingRequest->request->get('curSortOrder', 'true'), FILTER_VALIDATE_BOOLEAN
            );

            if ($newSortBy == '-') {
                $newSortBy = $curSortBy;
                $newSortOrder = $curSortOrder;
            } else {
                $newSortOrder = ($newSortBy == $curSortBy) ? !$curSortOrder : true;
            }

            $tasks = new Tasks();
            $taskItems = $tasks->getTasks($pageIndex, $filter, $newSortBy, $newSortOrder);

            $data = [
                'currentPage' => $pageIndex,
                'sortBy' => $tasks->sortField,
                'sortOrder' => $tasks->sortOrder,
                'totalPages' => ceil( $tasks->getTaskCount($filter) / $tasks->tasksPerPage),
                'taskItems' => $taskItems,
                'user' => $tasks->getUserInfo()
            ];


            return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);
        }
        else return new Response('Unknown widget', 500);
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
            $rotatingFileHandler = new RotatingFileHandler(dirname(__DIR__) . self::LOG_FILE_NAME, 0, (int)$_ENV['BITRIX24_PHP_SDK_LOG_LEVEL']);
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
        $eventDispatcher->addListener(AuthTokenRenewedEvent::class, function (AuthTokenRenewedEvent $authTokenRenewedEvent): void {
            self::onAuthTokenRenewedEventListener($authTokenRenewedEvent);
        });
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
        self::getAuthRepository()->saveRenewedToken($authTokenRenewedEvent->getRenewedToken());

        self::getLog()->debug('onAuthTokenRenewedEventListener.finish');
    }

    /**
     * @throws InvalidArgumentException
     * @throws UnknownScopeCodeException
     * @throws WrongConfigurationException
     */
    public static function getB24Service(?Request $request = null): ServiceBuilder
    {
        // init bitrix24 service builder auth data from request
        if ($request instanceof Request) {
            self::getLog()->debug('getB24Service.authFromRequest');
            return ServiceBuilderFactory::createServiceBuilderFromPlacementRequest(
                $request,
                self::getApplicationProfile(),
                self::getEventDispatcher(),
                self::getLog(),
            );
        }

        // init bitrix24 service builder auth data from saved auth token
        self::getLog()->debug('getB24Service.authFromAuthRepositoryStorage');
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
        // you can find list of local apps by this URL
        // https://YOUR-PORTAL-URL.bitrix24.com/devops/list/
        // or see in left menu Developer resources → Integrations → select target local applicatoin

        // load config: application secrets, logging
        self::loadConfigFromEnvFile();

        try {
            $profile = ApplicationProfile::initFromArray($_ENV);
            self::getLog()->debug('getApplicationProfile.finish');
            return $profile;
        } catch (InvalidArgumentException $invalidArgumentException) {
            self::getLog()->error('getApplicationProfile.error',
                [
                    'message' => sprintf('cannot read config from $_ENV: %s', $invalidArgumentException->getMessage()),
                    'trace' => $invalidArgumentException->getTraceAsString()
                ]);
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