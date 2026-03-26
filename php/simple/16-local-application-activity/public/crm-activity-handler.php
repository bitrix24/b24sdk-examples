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

use App\Controller\Bitrix24EventController;
use App\DI\DI;
use App\Repository\AuthRepositoryFactory;
use App\Robots\RobotHandler;
use App\Workflow\Activities\ActivityHandler;
use App\Workflow\Activities\ActivityRequest;
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotRequest;
use Bitrix24\SDK\Services\Workflows\Robot\Request\IncomingRobotRequest;
use Bitrix24\SDK\Services\Workflows\Workflow\Request\IncomingWorkflowRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__) . '/vendor/autoload.php';
$logger = DI::get(LoggerInterface::class);
try {
    $logger->debug('crm-activity-handler.php', [
        'request' => $_REQUEST,
    ]);

// start process request
    $incomingRequest = Request::createFromGlobals();
    $logger->debug(
        'ActivityHandler.start',
        [
            'request' => $incomingRequest->request->all(),
            'query' => $incomingRequest->query->all()
        ]
    );

// уровень контроллера
// todo check naming conventions
    $incomingActivityReq = IncomingWorkflowRequest::initFromRequest($incomingRequest);

// принимаем решение, что делаем и как обрабатываем

// уровень хендлера
    $activityRequest = ActivityRequest::initFromIncomingActivityRequest($incomingActivityReq);

    $logger->debug('activityRequest', [
        'code' => $activityRequest->code,
        'prop' => $activityRequest->properties,
        'eventToken' => $activityRequest->eventToken
    ]);

    /**
     * @var ActivityHandler $activityHandler
     */
    $activityHandler = DI::get(ActivityHandler::class);

    $activityHandler->handle($activityRequest);
} catch (\Throwable $e) {
    $logger->error($e->getMessage(), [
        'exception' => $e,
        'trace' => $e->getTraceAsString()
    ]);
}