<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';

// start process request
$incomingRequest = Request::createFromGlobals();
Application::getLog()->debug('install.init', [
	'request' => $incomingRequest->request->all(),
	'query' => $incomingRequest->query->all()
]);

$response = Application::processInstallation($incomingRequest);
$response->send();
?>