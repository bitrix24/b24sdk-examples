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

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!array_key_exists('BITRIX24_PHP_SDK_LOG_LEVEL', $_ENV)) {
    throw new InvalidArgumentException('in $_ENV variables not found key BITRIX24_PHP_SDK_LOG_LEVEL');
}

if (!array_key_exists('BITRIX24_PHP_SDK_LOG_MAX_FILES_COUNT', $_ENV)) {
    throw new InvalidArgumentException('in $_ENV variables not found key BITRIX24_PHP_SDK_LOG_MAX_FILES_COUNT');
}

// init psr-3 compatible logger
// https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
//
// channels:
// - App: application channel
// - b24-php-sdk: bitrix24-php-sdk channel
$logger = new Logger('App');
// rotating
// in production you MUST use logrotate or other specific util
$rotatingFileHandler = new RotatingFileHandler(
    '/var/logs/b24-php-sdk.log',
    (int)$_ENV['BITRIX24_PHP_SDK_LOG_MAX_FILES_COUNT'],
    Logger::toMonologLevel($_ENV['BITRIX24_PHP_SDK_LOG_LEVEL'])
);
$rotatingFileHandler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
$logger->pushHandler($rotatingFileHandler);
$logger->pushProcessor(new MemoryUsageProcessor(true, true));
$logger->pushProcessor(new UidProcessor());
$logger->pushProcessor(new IntrospectionProcessor());

try {
    print('Show all env variables:');
    print_r($_ENV);
    print('=====================' . PHP_EOL);
    $logger->info('Hello from application!');
    // init bitrix24-php-sdk service from webhook
    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook(
        $_ENV['BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL'],
        null,
        $logger->withName('b24-php-sdk')
    );

    // call any api method from universal interface core->call
    var_dump($b24Service->core->call('profile')->getResponseData()->getResult());

    // call method crm.lead.add from scope CRM
    $addedLeadId = $b24Service->getCRMScope()->lead()->add([
        'TITLE' => 'New Lead from cURL',
        'NAME' => 'John',
        'LAST_NAME' => 'Doe',
        'STATUS_ID' => 'NEW',
        'OPENED' => 'Y',
        'ASSIGNED_BY_ID' => 1,
        'PHONE' => [
            ['VALUE' => '+1234567890', 'VALUE_TYPE' => 'WORK']
        ],
        'EMAIL' => [
            ['VALUE' => 'test@example.com', 'VALUE_TYPE' => 'WORK']
        ]
    ])->getId();
    print(sprintf('added lead, id: %s', $addedLeadId) . PHP_EOL);
    // good structured logging
    $logger->info('app.lead.added', ['b24LeadId' => $addedLeadId]);
    // bad logging
    $logger->info(sprintf('added lead, id: %s', $addedLeadId));

    // read data from bitrix24
    $leadData = $b24Service->getCRMScope()->lead()->get($addedLeadId)->lead();
    print(sprintf('lead id %s', $leadData->ID) . PHP_EOL);
    print(sprintf('lead title: %s', $leadData->TITLE) . PHP_EOL);
    print_r($leadData);
    $logger->info('Bye-bye!');

    // call undefined function
    fooo();
} catch (InvalidArgumentException $exception) {
    $logger->critical('app.configuration.problem', ['exception' => $exception]
    );
    print(sprintf('ERROR IN CONFIGURATION OR CALL ARGS: %s', $exception->getMessage()) . PHP_EOL);
    print($exception::class . PHP_EOL);
    print($exception->getTraceAsString());
} catch (Throwable $throwable) {
    $logger->critical('app.failure', ['exception' => $throwable,]);
    print(sprintf('FATAL ERROR: %s', $throwable->getMessage()) . PHP_EOL);
    print($throwable::class . PHP_EOL);
    print($throwable->getTraceAsString());
}