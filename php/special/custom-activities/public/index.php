<?php

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
	<div class="mt-2xl">
		<div class="px-4 py-4 bg-white rounded-md">
			<h3 class="text-2xl font-semibold text-base-900">The application is running</h3>
			<p class="mt-1 max-w-2xl text-sm/6 text-base-500">Authentication tokens from Bitrix24</p>
			<pre
				class="rounded-md mt-4 p-4 bg-base-900/[50] text-green-350 font-semibold text-xs leading-4 overflow-auto font-b24-system-mono"
			><?php
				print_r($_REQUEST);
			?></pre>
		</div>
	</div>

<script src="https://unpkg.com/@bitrix24/b24jssdk@latest/dist/umd/index.min.js"></script>
<script type="module">
try
{
	const $logger = B24Js.LoggerBrowser.build(
		'local-apps: token-storage-in-file : index',
		true
	);
	
	const $b24 = await B24Js.initializeB24Frame();
	$b24.setLogger(
		B24Js.LoggerBrowser.build('Core')
	);
	
	$logger.warn('B24Frame.init');
	
	const response = await $b24.callMethod('server.time');
	const serverTimeResponse = response.getData().result;
	const serverDateTime = B24Js.Text.toDateTime(serverTimeResponse);
	
	$logger.info(
		`${B24Js.Text.getDateForLog()} serverTime >> `,
		serverDateTime.toFormat('y-MM-dd HH:mm:ss')
	);
}
catch( error )
{
	console.error(error);
}
</script>
<?php //*/?>
<?php require_once '../layouts/footer.php';
