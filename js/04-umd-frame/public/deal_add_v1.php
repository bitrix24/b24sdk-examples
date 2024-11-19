<?php declare(strict_types=1);
require_once '../layouts/header.php';
?>
<div class="mt-2xl flex flex-col">
	<div class="mb-md">
		<div class="grid grid-cols-[repeat(auto-fill,minmax(266px,1fr))] gap-y-sm gap-x-xs">
			<a href="./index.php" class="bg-white py-sm2 px-xs2 cursor-pointer rounded-md flex flex-row flex-nowrap items-center justify-start gap-sm border-2 transition-shadow shadow hover:shadow-lg relative border-white hover:border-primary">
				<div class="rounded-full bg-base-100 size-7 min-w-7 min-h-7 flex items-center justify-center">
					<svg class="size-4 text-base-400 dark:text-base-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="m15.861 17.658-4.527-4.528L10.2 12l1.134-1.129 4.527-4.527-1.597-1.597L7.009 12l7.255 7.254z" clip-rule="evenodd"/></svg>
				</div>
				<div class="max-w-11/12 font-b24-secondary text-black text-h6 leading-4 mb-0 font-semibold">Main page</div>
			</a>
		</div>
	</div>
</div>
<div class="mt-xl">
	<div class="px-4 py-4 bg-white rounded-md">
		<h3 class="text-2xl font-semibold text-base-900">Add Deal by crm.deal.add</h3>
		<p class="mt-1 max-w-2xl text-sm/6 text-base-500">Demonstrates how to add a deal by B24Js</p>
		<pre id="logResponse" class="hidden max-h-[250px] rounded-md mt-4 p-4 bg-base-900/[50] text-green-350 font-semibold text-xs leading-4 overflow-auto font-b24-system-mono"></pre>
		<div id="error" class="hidden mt-lg px-lg py-md rounded-md text-white bg-red-500 flex flex-row flex-nowrap gap-4 items-center justify-start">
			<div class="rounded-full border-2 border-red-400 bg-white size-5xl min-w-10 min-h-10 flex items-center justify-center">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="-4 -5 26 26" fill="currentColor" class="size-2xl text-red-500"><path fill="currentColor" fill-rule="evenodd" d="M17.5 12.84 10.223.72c-.561-.93-1.897-.93-2.446 0L.5 12.84C-.073 13.795.62 15 1.729 15h14.554c1.098 0 1.79-1.205 1.217-2.16M7.956 4.943c0-.537.43-.967.966-.967h.132c.536 0 .966.43.966.967v3.614c0 .537-.43.967-.966.967h-.132a.96.96 0 0 1-.966-.967zm2.255 7.014c0 .669-.549 1.217-1.217 1.217a1.22 1.22 0 0 1-1.217-1.217c0-.668.549-1.216 1.217-1.216s1.217.548 1.217 1.216"/></svg>
			</div>
			<div>
				<h3 class="text-h3 mb-1">Error</h3>
				<div class="text-txt-md" id="errorMessage"></div>
			</div>
		</div>
	</div>
</div>
<script type="module">

const errorNode = document.getElementById('error');
const errorMessageNode = document.getElementById('errorMessage');
if(errorNode && errorMessageNode)
{
	errorNode.classList.add('hidden');
	errorMessageNode.innerHTML = '';
}

const logNode = document.getElementById('logResponse');
if(logNode)
{
	errorNode.classList.add('hidden');
}

try
{
	const $logger = B24Js.LoggerBrowser.build(
		'local-apps: token-storage-in-file : deal_add_v1',
		true
	);
	
	const $b24 = await B24Js.initializeB24Frame();
	$b24.setLogger(
		B24Js.LoggerBrowser.build('Core')
	);
	
	$logger.warn('B24Frame.init');
	
	const params = {
		fields: {
			TITLE: `New Deal ${B24Js.Text.getUuidRfc4122()}`,
			TYPE_ID: 'SALE',
			STAGE_ID: 'NEW'
		}
	};
	
	const response = await $b24.callMethod(
		'crm.deal.add',
		params
	);
	
	const newDealResponse = response.getData().result;
	const newDealId = B24Js.Text.toInteger(newDealResponse);
	
	$logger.info(
		`${B24Js.Text.getDateForLog()} crm.deal.add >> ${newDealId}`
	);
	
	$b24.slider.openPath(
		$b24.slider.getUrl(`/crm/deal/details/${newDealId}/`),
		950
	);
	
	if(logNode)
	{
		logNode.classList.remove('hidden');
		logNode.innerHTML = JSON.stringify({
			send: {
				method: 'crm.deal.add',
				params
			},
			response: newDealId
		}, null, 2);
	}
}
catch( error )
{
	console.error(error);
	
	if(errorNode && errorMessageNode)
	{
		errorNode.classList.remove('hidden');
		errorMessageNode.innerHTML = error?.message || error;
	}
}
</script>
<?php require_once '../layouts/footer.php';
