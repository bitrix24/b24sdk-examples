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
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotRequest;
use Bitrix24\SDK\Services\Workflows\Robot\Request\IncomingRobotRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$logger = DI::get(LoggerInterface::class);
$logger->debug('crm-robot-handler.php', [
    'request' => $_REQUEST,
]);

// start process request
$incomingRequest = Request::createFromGlobals();
$logger->debug(
    'RobotHandlerSimple.start',
    [
        'request' => $incomingRequest->request->all(),
        'query' => $incomingRequest->query->all()
    ]
);

// уровень контроллера
$incomingRobotReq = IncomingRobotRequest::initFromRequest($incomingRequest);

// принимаем решение, что делаем и как обрабатываем

// уровень хендлера
$robotRequest = RobotRequest::initFromIncomingRobotRequest($incomingRobotReq);

$logger->debug('robotRequest', [
    'code' => $robotRequest->code,
    'prop' => $robotRequest->properties,
    'eventToken' => $robotRequest->eventToken
]);

/**
 * @var RobotHandler $robotHandler
 */
$robotHandler = DI::get(RobotHandler::class);

$robotHandler->handle($robotRequest);

