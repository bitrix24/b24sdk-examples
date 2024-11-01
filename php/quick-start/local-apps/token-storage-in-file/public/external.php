<?php

declare(strict_types=1);

namespace App;

require_once dirname(__DIR__).'/vendor/autoload.php';

// Retrieve the Bitrix24 service.
$B24 = Application::getB24Service();

// Retrieve the current user profile.
$result = $B24->getMainScope()->main()->getCurrentUserProfile()->getUserProfile();

require_once '../layouts/header.php'
?>
	<div class="row">
		<div class="col-md-12 col-lg-12 text-small">
			
			<h2>User Profile</h2>
			<p>
				name: <?=$result->NAME; ?><br>
				last name: <?=$result->LAST_NAME; ?><br>
				is admin: <?=$result->ADMIN
					? 'true'
					: 'false';
				?>
			</p>
			<h2>Request</h2>
			<pre><?php print_r($_REQUEST); ?></pre>
		</div>
	</div>
<?php
require_once '../layouts/footer.php';