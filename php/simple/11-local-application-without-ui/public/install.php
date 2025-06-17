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

use App\Controller\InstallController;
use App\Repository\AuthRepositoryFactory;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// add to log all incoming requests
$logger = LoggerFactory::create();
$logger->debug('install.php', [
    'request' => $_REQUEST,
]);

// start process request
$incomingRequest = Request::createFromGlobals();
$logger->debug(
    'install.start',
    [
        'request' => $incomingRequest->request->all(),
        'query' => $incomingRequest->query->all()
    ]
);

$installController = new InstallController(AuthRepositoryFactory::create($logger), $logger);
$resp = $installController->process($incomingRequest);
$resp->send();