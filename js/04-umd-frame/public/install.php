<?php declare(strict_types=1);

require_once '../layouts/header.php'
?>
<div class="mt-2xl">
	<div class="px-4 py-4 bg-white rounded-md">
		<h3 class="text-2xl font-semibold text-base-900">The application installation has begun</h3>
	</div>
</div>
<script type="module">
try
{
	const $b24 = await B24Js.initializeB24Frame();
	$b24.installFinish();
}
catch( error )
{
	console.error(error);
}
</script>
<?php require_once '../layouts/footer.php';