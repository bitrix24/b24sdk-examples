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

require_once dirname(__DIR__).'/vendor/autoload.php';

$incomingRequest = Request::createFromGlobals();
Application::getLog()->debug('index.init', [
	'request' => $incomingRequest->request->all(),
	'query' => $incomingRequest->query->all()
]);

require_once '../layouts/header.php';
?>
    <div class="mt-14px">
        <div class="container my-2xl mx-6 px-4 py-4 bg-primary-on rounded-md">
            <h3 class="text-2xl font-semibold text-base-900">The application is running</h3>
            <p class="mt-1 max-w-2xl text-sm/6 text-base-500">Authentication tokens from Bitrix24</p>
	        <pre class="mt-4 rounded-md px-4 py-4 bg-base-600 text-green-350 text-sm/6 overflow-auto"><?php print_r($_REQUEST); ?></pre>
        </div>
    </div>
<?php
require_once '../layouts/footer.php';
