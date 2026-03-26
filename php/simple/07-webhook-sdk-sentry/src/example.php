<?php


/**
 * This file is part of the b24sdk-examples package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use App\DI\DI;
use App\Services\Alpha;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Psr\Log\LoggerInterface;
use Sentry\State\Scope;

use function Sentry\configureScope;
use function Sentry\init;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// configure sentry in bootstrap
init([
    'dsn' => $_ENV['SENTRY_DSN'],
    // Additional options as needed
    'release' => $_ENV['SENTRY_RELEASE'],
    'environment' => $_ENV['SENTRY_ENVIRONMENT'],
]);

/**
 * @var LoggerInterface $logger
 */
$logger = DI::get(LoggerInterface::class);
$logger->info("============================");

$logger->debug('start application');

try {
    print('Show all env variables:');
    print_r($_ENV);
    print('=====================' . PHP_EOL);

    // init bitrix24-php-sdk service from webhook
    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook(
        $_ENV['BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL'],
        null,
        $logger
    );

    // call any api method from universal interface core->call
    var_dump($b24Service->core->call('profile')->getResponseData()->getResult());
    $currentB24User = $b24Service->getMainScope()->main()->getCurrentUserProfile()->getUserProfile();

    // add tag to sentry
    configureScope(function (Scope $scope): void {
        global $b24Service;
        $scope->setTag('b24.portalUrl', $b24Service->core->getApiClient()->getCredentials()->getDomainUrl());
    });
    // add current user metadata to sentry traces
    configureScope(function (Scope $scope): void {
        global $currentB24User;
        $scope->setUser([
            'id' => $currentB24User->ID,
            // be careful to send personal data to another system
            'username' => sprintf('%s %s', $currentB24User->NAME, $currentB24User->LAST_NAME),
            'is-admin' => $currentB24User->ADMIN
        ]);
    });

    // call method crm.lead.add from scope CRM
    $addedLeadId = $b24Service->getCRMScope()->lead()->add([
        'TITLE' => 'New Lead from cURL',
        'NAME' => 'John',
        'LAST_NAME' => 'Doe',
        'STATUS_ID' => 'NEW',
        'OPENED' => 'Y',
        'ASSIGNED_BY_ID' => 1,
//        'PHONE' => [
//            ['VALUE' => '+1234567890', 'VALUE_TYPE' => 'WORK']
//        ],
//        'EMAIL' => [
//            ['VALUE' => 'test@example.com', 'VALUE_TYPE' => 'WORK']
//        ]
    ])->getId();
    print(sprintf('added lead id: %s', $addedLeadId) . PHP_EOL);

    // read data from bitrix24
    $leadData = $b24Service->getCRMScope()->lead()->get($addedLeadId)->lead();
    print(sprintf('lead id %s', $leadData->ID) . PHP_EOL);
    print(sprintf('lead title: %s', $leadData->TITLE) . PHP_EOL);
    print_r($leadData);

    // get fatal error
    /**
     * @var Alpha $alphaService
     */
    $alphaService = DI::get(Alpha::class);
    $alphaService->run();
} catch (InvalidArgumentException $exception) {
    $logger->critical(
        sprintf('configuration error: %s', $exception->getMessage()),
        ['exception' => $exception]
    );
    print(sprintf('ERROR IN CONFIGURATION OR CALL ARGS: %s', $exception->getMessage()) . PHP_EOL);
    print($exception::class . PHP_EOL);
    print($exception->getTraceAsString());
} catch (Throwable $throwable) {
    $logger->critical(
        sprintf('fatal error: %s', $throwable->getMessage()),
        ['exception' => $throwable]
    );
    print(sprintf('FATAL ERROR: %s', $throwable->getMessage()) . PHP_EOL);
    print($throwable::class . PHP_EOL);
    print($throwable->getTraceAsString());
}
