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

require_once '../layouts/header.php'
?>
	<div class="row">
		<div class="col-md-12 col-lg-12 text-small">
			<h2>Request</h2>
			<p>The application is running.</p>
			
			<p>Authentication tokens from Bitrix24:</p>
			<pre><?=print_r($_REQUEST, true)?></pre>
		</div>
	</div>
<?php
require_once '../layouts/footer.php';
