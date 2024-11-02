<?php

declare(strict_types=1);

namespace App;

require_once dirname(__DIR__).'/vendor/autoload.php';

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
            <table class="text-left table-auto">
                <thead>
                    <tr class="border-b border-gray-300">
                        <th class="py-2 px-1 w-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="size-5 text-base-master dark:text-base-200"><path fill="currentColor" fill-rule="evenodd" d="M20 13.443v-1.888h-1.828a5.7 5.7 0 0 0-.982-2.41l1.282-1.28-1.335-1.335-1.282 1.28a5.7 5.7 0 0 0-2.397-1.003V5h-1.886v1.802a5.7 5.7 0 0 0-2.422.99L7.875 6.52 6.54 7.855l1.275 1.271a5.7 5.7 0 0 0-1 2.429H5v1.888h1.815c.15.888.495 1.705 1.001 2.406L6.53 17.134l1.335 1.334 1.287-1.287c.708.502 1.529.84 2.42.985V20h1.885v-1.84a5.7 5.7 0 0 0 2.394-.994l1.294 1.295 1.339-1.336-1.3-1.297c.499-.695.84-1.506.988-2.387h1.828zm-7.502 2.556a3.499 3.499 0 0 1 0-6.999 3.5 3.5 0 1 1 .002 7z" clip-rule="evenodd"/></svg>
                        </th>
                        <th class="py-2 px-4 text-sm/6 font-semibold text-base-900">Parameter</th>
                        <th class="py-2 px-4 text-sm/6 font-semibold text-base-900">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-2 px-1 border-b border-gray-300 text-sm/6 font-medium text-base-900">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="size-5 text-base-master dark:text-base-200"><path fill="currentColor" fill-rule="evenodd" d="M4 5h17v2H4zm0 6h17v2H4zm17 6H4v2h17z" clip-rule="evenodd"/></svg>
                        </td>
                        <td class="py-2 px-4 border-b border-gray-300 text-sm/6 font-medium text-base-900">Name</td>
                        <td class="py-2 px-4 border-b border-gray-300 text-sm/6 font-medium text-base-900"><?=$result?->NAME?></td>
                        
                    </tr>
                    <tr>
                    <td class="py-2 px-1 border-b border-gray-300 text-sm/6 font-medium text-base-900">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="size-5 text-base-master dark:text-base-200"><path fill="currentColor" fill-rule="evenodd" d="M4 5h17v2H4zm0 6h17v2H4zm17 6H4v2h17z" clip-rule="evenodd"/></svg>
                        </td>
                        <td class="py-2 px-4 border-b border-gray-300 text-sm/6 font-medium text-base-900">Last Name</td>
                        <td class="py-2 px-4 border-b border-gray-300 text-sm/6 font-medium text-base-900"><?=$result?->LAST_NAME?></td>
                        
                    </tr>
                    <tr>
                        <td class="py-2 px-1 text-sm/6 font-medium text-base-900">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="size-5 text-base-master dark:text-base-200"><path fill="currentColor" fill-rule="evenodd" d="M4 5h17v2H4zm0 6h17v2H4zm17 6H4v2h17z" clip-rule="evenodd"/></svg>
                        </td>
                        <td class="py-2 px-4 text-sm/6 font-medium text-base-900">Is Admin</td>
                        <td class="py-2 px-4 text-sm/6 font-medium text-base-900 <?=$result?->ADMIN ? 'text-green-500' : 'text-red-500';?>"><?=$result?->ADMIN ? 'True' : 'False';?></td>
                        
                    </tr>
                </tbody>
            </table>
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