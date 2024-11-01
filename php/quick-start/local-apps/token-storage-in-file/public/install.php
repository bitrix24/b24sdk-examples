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
Application::getLog()->debug('install.init', [
	'request' => $incomingRequest->request->all(),
	'query' => $incomingRequest->query->all()
]);

Application::processInstallation($incomingRequest);

require_once '../layouts/header.php'
?>
	<p>The application installation has begun.</p>
	<h3 class="text-h1 mb-sm flex whitespace-pre-wrap">Tokens received from Bitrix24:</h3>
	<pre class="rounded-md px-4 py-4 bg-base-900 text-green-350 overflow-auto"><?php print_r($_REQUEST); ?></pre>
<script src="https://api.bitrix24.com/api/v1/"></script>
<script>
	BX24.init(function() {
		BX24.installFinish();
	});
</script>
<!-- script type="module" crossorigin>
	import {
		B24Frame
	} from './tmp/b24jssdk/index.mjs'
	
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
		
		const b24Frame = new B24Frame(queryParams)
		await b24Frame.init()
		
		return b24Frame
	}
	
	try
	{
		const $b24 = await initializeB24Frame()
		$b24.installFinish()
	}
	catch( error )
	{
		console.error(error)
	}
</script -->
	<?php
require_once '../layouts/footer.php';