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
	<p>The application is running.</p>
	<h3 class="text-h1 mb-sm flex whitespace-pre-wrap">Authentication tokens from Bitrix24:</h3>
	<pre class="rounded-md px-4 py-4 bg-base-900 text-green-350 overflow-auto"><?php print_r($_REQUEST); ?></pre>
<?php
require_once '../layouts/footer.php';
