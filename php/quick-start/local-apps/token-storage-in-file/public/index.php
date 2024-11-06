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
<script src="/tmp/b24jssdk/browser.index.js"></script>
<script type="module">
	const initializeB24Frame = async () =>
	{
		const queryParams = {
			DOMAIN: null,
			PROTOCOL: false,
			APP_SID: null,
			LANG: null
		}
		
		if(window.name)
		{
			const [domain, protocol, appSid] = window.name.split('|')
			queryParams.DOMAIN = domain
			queryParams.PROTOCOL = parseInt(protocol) === 1
			queryParams.APP_SID = appSid
			queryParams.LANG = null
		}
		
		if(!queryParams.DOMAIN || !queryParams.APP_SID)
		{
			throw new Error('Unable to initialize Bitrix24Frame library!')
		}
		
		const b24Frame = new B24Js.B24Frame(queryParams)
		await b24Frame.init()
		
		return b24Frame
	}
	
	try
	{
		const $logger = B24Js.LoggerBrowser.build(
			'local-apps: token-storage-in-file : index',
			true
		)
		
		const $b24 = await initializeB24Frame()
		$b24.setLogger(
			B24Js.LoggerBrowser.build('Core', true)
		)
		
		$b24.callMethod('server.time')
		.then((response) => {
			const serverTimeResponse = response.getData().result;
			const serverDateTime = B24Js.Text.toDateTime(serverTimeResponse)
			$logger.info(
				'serverTime >> ',
				serverDateTime.toFormat('y-MM-dd HH:mm:ss')
			)
		})
	}
	catch( error )
	{
		console.error(error)
	}
</script>
<?php require_once '../layouts/footer.php';
