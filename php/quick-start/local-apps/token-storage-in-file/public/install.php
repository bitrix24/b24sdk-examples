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

require_once dirname(__DIR__). '/vendor/autoload.php';

$incomingRequest = Request::createFromGlobals();
Application::getLog()->debug('install.init', ['request' => $incomingRequest->request->all(), 'query' => $incomingRequest->query->all()]);

Application::processInstallation($incomingRequest);

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
            The application installation has begun. Tokens received from Bitrix24:
            <pre>
                <?= print_r($_REQUEST, true) ?>
            </pre>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
        <script src="//api.bitrix24.com/api/v1/"></script>
<script>
    BX24.init(function() {
        BX24.installFinish();
    });
</script>
</body>
</html>