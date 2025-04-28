<?php

declare(strict_types=1);

use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Symfony\Component\HttpFoundation\Request;

require_once '../vendor/autoload.php';

$appProfile = ApplicationProfile::initFromArray([
    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => 'put-your-client-id-here', // put-your-client-id-here
    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => 'put-your-client-secret-here', // put-your-client-secret-here
    'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => 'crm,user_basic'
]);

$B24 = ServiceBuilderFactory::createServiceBuilderFromPlacementRequest(
    Request::createFromGlobals(), 
    $appProfile
);

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
    
    <div class="mt-2xl">
        <div class="px-4 py-4 bg-white rounded-md mb-2xl">
            <h3 class="text-2xl font-semibold text-base-900">Deals</h3>
            
            <table class="w-1/2 table-auto mt-4 border-collapse border border-gray-300">
                <thead>
                    <tr>
                        <th class="text-left border border-gray-300 px-4 py-2" scope="col">#</th>
                        <th class="text-left border border-gray-300 px-4 py-2" scope="col">Title</th>
                        <th class="text-left border border-gray-300 px-4 py-2" scope="col">Amount</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $deals = $B24->getCRMScope()->deal()->list(
                        ['ID' => 'ASC'], 
                        [],
                        ['ID', 'TITLE', 'OPPORTUNITY']
                    )->getDeals();

                    foreach ($deals as $deal) {
                        ?>
                        <tr>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $deal->ID; ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $deal->TITLE; ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $deal->OPPORTUNITY; ?></td>
                        </tr>       
                        <?php
                    }
                ?>
                </tbody>
            </table>

        </div>
    </div>

<?php require_once '../layouts/footer.php';