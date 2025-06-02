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
use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotRequest;
use Bitrix24\SDK\Services\Workflows\Robot\Request\IncomingRobotRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// add to log all incoming requests
$logger = LoggerFactory::create();
$logger->debug('event-handler.php', [
    'request' => $_REQUEST,
]);

$ob = DI::get(AuthRepositoryFactory::class);

var_dump($ob);