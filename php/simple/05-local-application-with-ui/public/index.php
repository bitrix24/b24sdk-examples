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
use App\Controller\PlacementController;
use App\Repository\AuthRepositoryFactory;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// load config from env file
Bootstrap::loadConfigFromEnvFile();
$log = LoggerFactory::create();
// create placement controller
$placementController = new PlacementController($log);

$incomingRequest = Request::createFromGlobals();
$log->debug('index.init', [
    'request' => $incomingRequest->request->all(),
    'query' => $incomingRequest->query->all()
]);
// process request with controller
$result = $placementController->process($incomingRequest);
?>

<pre>
    Main placement, auth tokens from bitrix24:
    <?= print_r($_REQUEST, true) ?>
    Results from placement controller:
<?php
$result->send();
?>
</pre>
