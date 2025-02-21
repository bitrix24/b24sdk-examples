<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$incomingRequest = Request::createFromGlobals();
Application::getLog()->debug(
    'install.init',
    ['request' => $incomingRequest->request->all(), 'query' => $incomingRequest->query->all()]
);

$response = Application::processRequest($incomingRequest);
?>
    <pre>
    Application installation started, tokens from Bitrix24:
    <?= print_r($_REQUEST, true) ?>
    Now we saved auth tokens from Bitrix24 and try to finalize application installation via javascript call method BX24.installFinish()
</pre>
    <script src="//api.bitrix24.com/api/v1/"></script>
    <script>
        BX24.installFinish();
    </script>
    <?php
$response->send();
?>