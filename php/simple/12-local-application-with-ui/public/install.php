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
use Fig\Http\Message\StatusCodeInterface;
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
$response = $installController->process($incomingRequest);
?>
<?php
if ($response->getStatusCode() !== StatusCodeInterface::STATUS_OK): ?>
    <pre>
        Oh no! something went wrong (ノಠ益ಠ)ノ彡┻━┻
        try to find reason in log files in folder var/logs/
    <?php
    $response->send()
    ?>
    </pre>
<?php
else: ?>
    <pre>
        Application installation finished, tokens from Bitrix24:
        <?= print_r($_REQUEST, true) ?>
        Now we save auth tokens from Bitrix24 and try to finalize application installation via javascript call method BX24.installFinish()
    </pre>
    <script src="//api.bitrix24.com/api/v1/"></script>
    <script>
        BX24.installFinish();
    </script>
    <?php
    //send response to stdout
    $response->send();
    ?>
<?php
endif; ?>
