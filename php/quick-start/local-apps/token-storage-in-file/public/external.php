<?php

declare(strict_types=1);

namespace App;

/*/
require_once dirname(__DIR__).'/vendor/autoload.php';
//*/

$errorInfo = null;
$result = null;

try
{
	// Retrieve the Bitrix24 service.
	$B24 = Application::getB24Service();
	
	// Retrieve the current user profile.
	$result = $B24->getMainScope()->main()->getCurrentUserProfile()->getUserProfile();
}
catch(\Exception $exception)
{
	$errorInfo = sprintf("Exception: %s <br><small>Line: %s<br>File: %s<small>", $exception->getMessage(), $exception->getLine(), $exception->getFile(),);
}

require_once '../layouts/header.php'
?>
	<?php if(!empty($errorInfo)): ?>
	<div class="mt-sm2">
		<div class="my-2xl mx-6 px-4 py-4 bg-white rounded-md">
			<div class="border border-red p-4 rounded-md text-lg"><?=$errorInfo?></div>
		</div>
	</div>
<?php else: ?>
	<div class="mt-2xl">
		<div class="px-4 py-4 bg-white rounded-md">
			<div class="px-4 sm:px-0">
				<h3 class="text-2xl font-semibold text-base-900">The application is running</h3>
				<p class="mt-1 max-w-2xl text-sm/6 text-base-500">User Profile</p>
			</div>
<?php /*/?>
			<div class="mt-6 px-4 border-t rounded-md border border-base-200 bg-white">
				<dl class="divide-y divide-base-100">
					<div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
						<dt class="text-sm/6 font-medium text-base-900">name</dt>
						<dd class="mt-1 text-sm/6 text-base-700 sm:col-span-2 sm:mt-0"><?=$result?->NAME?></dd>
					</div>
					<div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
						<dt class="text-sm/6 font-medium text-base-900">last name</dt>
						<dd class="mt-1 text-sm/6 text-base-700 sm:col-span-2 sm:mt-0"><?=$result?->LAST_NAME?></dd>
					</div>
					<div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
						<dt class="text-sm/6 font-medium text-base-900">is admin</dt>
						<dd class="mt-1 text-sm/6 text-base-700 sm:col-span-2 sm:mt-0 <?=$result?->ADMIN ? 'text-green-500' : 'text-red-500';?> "><?=$result?->ADMIN ? 'true' : 'false';?></dd>
					</div>
				</dl>
			</div>
<?php //*/?>
		</div>
	</div>
	<div class="mt-2xl">
		<div class="px-4 py-4 bg-white rounded-md">
			<h3 class="text-2xl font-semibold text-base-900">Request</h3>
			<pre
				class="rounded-md mt-4 p-4 bg-base-900/[50] text-green-350 font-semibold text-xs leading-4 overflow-auto font-b24-system-mono"
			><?php
				print_r($_REQUEST);
			?></pre>
		</div>
	</div>
<?php endif; ?>
<?php require_once '../layouts/footer.php';