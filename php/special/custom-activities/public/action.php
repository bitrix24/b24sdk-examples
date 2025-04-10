<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';

$incomingRequest = Request::createFromGlobals();

Application::getLog()->debug('action.init', [
	'request' => $incomingRequest->request->all(),
	'query' => $incomingRequest->query->all()
]);

if ($incomingRequest->request->has('authJson'))
{
    $auth = json_decode($incomingRequest->request->get('authJson'), true);
    $incomingRequest->request->add($auth);

    Application::getLog()->debug('action.init', ['authJson' => $incomingRequest->request->get('authJson')]);
}   
$B24 = Application::getB24Service($incomingRequest);

$result = [
    'result' => 'success'
];

$document = new Document($B24, Application::getLog());
$externalDocumentId = $document->registerDocument(
    $incomingRequest->request->getInt('documentId'),
    $incomingRequest->request->get('employeeEmail'),
    $incomingRequest->request->get('contactEmail')
);

if ($externalDocumentId > 0)
{
    $document->createActivity(
        $incomingRequest->request->getInt('documentId'),
        $incomingRequest->request->get('employeeEmail'),
        $incomingRequest->request->get('contactEmail'),
        $externalDocumentId,
        $incomingRequest->request->getInt('dealId')
    );
}

header('Content-Type: application/json');
echo json_encode($result);

?>