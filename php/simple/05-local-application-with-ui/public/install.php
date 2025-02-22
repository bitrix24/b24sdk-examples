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

// load config from env file
Bootstrap::loadConfigFromEnvFile();
$log = LoggerFactory::create();
$installController = new InstallController(AuthRepositoryFactory::create($log), $log);

// start process request
$incomingRequest = Request::createFromGlobals();
$log->debug(
    'install.start',
    [
        'request' => $incomingRequest->request->all(),
        'query' => $incomingRequest->query->all()
    ]
);

// process request:
// - save auth tokens in file - config/auth.json.local
// - subscribe to application lifecycle events
$response = $installController->process($incomingRequest);

// in production application you must use some template engine, but in this example we use php native templating features
?>

<?php if($response->getStatusCode() !== 200): ?>
    <pre>
        oh no! something went wrong
        try to find reason in log files in folder var/logs/
    <?php
    $response->send()
    ?>
    </pre>
<?php else: ?>
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
<?php endif; ?>