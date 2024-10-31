<?php

declare(strict_types=1);

namespace App;

require_once dirname(__DIR__). '/vendor/autoload.php';

// Retrieve the Bitrix24 service.
$B24 = Application::getB24Service();

// Retrieve the current user profile.
$result = $B24->getMainScope()
    ->main()
    ->getCurrentUserProfile()
    ->getUserProfile();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="css/app.css">
	<script
		src="https://code.jquery.com/jquery-3.6.0.js"
		integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
		crossorigin="anonymous"></script>

	<title>B24PhpSDK local-app demo</title>
</head>
<body class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-lg-12 text-small">
            <h2>Request</h2>
<pre>
<?php print_r($_REQUEST); ?>
</pre>
            <h2>User Profile</h2>
            <p>
                name: <?php echo $result->NAME; ?><br/>
                last name: <?php echo $result->LAST_NAME; ?><br/>
                is admin: <?php echo $result->ADMIN ? 'true' : 'false'; ?>
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
