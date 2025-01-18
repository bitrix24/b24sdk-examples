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
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;

require_once 'vendor/autoload.php';

// init psr-3 compatible logger
// https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
$logger = new Logger('App');
// rotating
// in production you MUST use logrotate or other specific util
$rotatingFileHandler = new RotatingFileHandler('b24-php-sdk.log', 30);
$rotatingFileHandler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
$logger->pushHandler($rotatingFileHandler);
$logger->pushProcessor(new MemoryUsageProcessor(true, true));
$logger->pushProcessor(new UidProcessor());

try {
    // init bitrix24-php-sdk service from webhook
    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook(
        'INSERT_HERE_WEBHOOK_URL',
        null,
        $logger
    );

    // call any api method from universal interface core->call
    var_dump($b24Service->core->call('user.current')->getResponseData()->getResult());

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
    print(sprintf('added lead id: %s', $addedLeadId) . PHP_EOL);

    // read data from bitrix24
    $leadData = $b24Service->getCRMScope()->lead()->get($addedLeadId)->lead();
    print(sprintf('lead id %s', $leadData->ID) . PHP_EOL);
    print(sprintf('lead title: %s', $leadData->TITLE) . PHP_EOL);
    print_r($leadData);
} catch (InvalidArgumentException $exception) {
    $logger->critical(
        sprintf('configuration error: %s', $exception->getMessage()),
        ['exception' => $exception->getTrace()]
    );
    print(sprintf('ERROR IN CONFIGURATION OR CALL ARGS: %s', $exception->getMessage()) . PHP_EOL);
    print($exception::class . PHP_EOL);
    print($exception->getTraceAsString());
} catch (Throwable $throwable) {
    $logger->critical(
        sprintf('fatal error: %s', $throwable->getMessage()),
        ['exception' => $throwable->getTrace()]
    );
    print(sprintf('FATAL ERROR: %s', $throwable->getMessage()) . PHP_EOL);
    print($throwable::class . PHP_EOL);
    print($throwable->getTraceAsString());
}