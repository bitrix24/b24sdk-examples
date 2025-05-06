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

require_once 'vendor/autoload.php';

try {
    print('Show all env variables:');
    print_r($_ENV);
    print('=====================' . PHP_EOL);

    // init bitrix24-php-sdk service from webhook
    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook($_ENV['BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL']);

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
    print(sprintf('added lead id: %s', $addedLeadId) . PHP_EOL);

    // read data from bitrix24
    $leadData = $b24Service->getCRMScope()->lead()->get($addedLeadId)->lead();
    print(sprintf('lead id %s', $leadData->ID) . PHP_EOL);
    print(sprintf('lead title: %s', $leadData->TITLE) . PHP_EOL);
    print_r($leadData);
} catch (InvalidArgumentException $exception) {
    print(sprintf('ERROR IN CONFIGURATION OR CALL ARGS: %s', $exception->getMessage()) . PHP_EOL);
    print($exception::class . PHP_EOL);
    print($exception->getTraceAsString());
} catch (Throwable $throwable) {
    print(sprintf('FATAL ERROR: %s', $throwable->getMessage()) . PHP_EOL);
    print($throwable::class . PHP_EOL);
    print($throwable->getTraceAsString());
}