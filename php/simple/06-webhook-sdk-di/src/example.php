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
use App\Services\Beta;
use App\Services\Gamma;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Psr\Log\LoggerInterface;

require_once dirname(__DIR__) . '/vendor/autoload.php';

//// init psr-3 compatible logger
//// https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
//$logger = new Logger('App');
//// rotating
//// in production, you MUST use logrotate or other specific util
//$rotatingFileHandler = new RotatingFileHandler('/var/logs/b24-php-sdk.log', 30);
//$rotatingFileHandler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
//$logger->pushHandler($rotatingFileHandler);
//$logger->pushProcessor(new MemoryUsageProcessor(true, true));
//$logger->pushProcessor(new UidProcessor());
//$logger->pushProcessor(new IntrospectionProcessor());
/**
 * @var LoggerInterface $logger
 */
$logger = DI::get(LoggerInterface::class);
$logger->info("============================");

// init object without factory method or DI
$alphaService = new Alpha(
    new Beta($logger),
    new Gamma($logger),
    $logger
);
$alphaService->run();
$logger->info("------------------");


// init object with factory method
$alphaService = Alpha::init($logger);
$alphaService->run();
$logger->info("------------------");

// init object with DI
/**
 * @var Alpha $alphaService
 */
$alphaService = DI::get(Alpha::class);
$alphaService->run();
exit();

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
} catch (InvalidArgumentException $exception) {
    $logger->critical('app.configuration.problem', ['exception' => $exception]);
    print(sprintf('ERROR IN CONFIGURATION OR CALL ARGS: %s', $exception->getMessage()) . PHP_EOL);
    print($exception::class . PHP_EOL);
    print($exception->getTraceAsString());
} catch (Throwable $throwable) {
    $logger->critical('app.failure', ['exception' => $throwable,]);
    print(sprintf('FATAL ERROR: %s', $throwable->getMessage()) . PHP_EOL);
    print($throwable::class . PHP_EOL);
    print($throwable->getTraceAsString());
}
