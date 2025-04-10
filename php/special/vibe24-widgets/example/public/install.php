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

require_once dirname(__DIR__). '/vendor/autoload.php';

$incomingRequest = Request::createFromGlobals();
Application::getLog()->debug('install.init', ['request' => $incomingRequest->request->all(), 'query' => $incomingRequest->query->all()]);

Application::processInstallation($incomingRequest);
Application::createWidgets('widgets');

?>
<pre>
    Application installation has been started. Tokens from Bitrix24:
    <?= print_r($_REQUEST, true) ?>
</pre>
<script src="//api.bitrix24.com/api/v1/"></script>
<script>
    BX24.init(function() {
        BX24.installFinish();
    });
</script>