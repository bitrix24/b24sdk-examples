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

use App\Controller\PlacementController;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// add to log all incoming requests
$logger = LoggerFactory::create();

$incomingRequest = Request::createFromGlobals();

$logger->debug('ajax.start', [
    'request' => $_REQUEST,
    'all' => $incomingRequest->request->all(),
    'payload' => $incomingRequest->getContent(),
]);

$sb = Bitrix24ServiceBuilderFactory::createFromStoredToken();

var_dump($sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile());


print(json_encode(['success' => true], JSON_THROW_ON_ERROR));


