<?php
declare(strict_types=1);

namespace App;

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// start process request
$request = Request::createFromGlobals();
$input = $request->request->all() ?: [];
$action = $request->query->get('action', '');

Application::getLog()->debug('index.init', [
	'request' => $request->request->all(),
	'query' => $request->query->all(),
    'input' => $input,
    'action' => $action
]);

$response = null;

/*
if (empty($input['connection']) || !is_array($input['connection'])) {
    throw new \InvalidArgumentException('Authorization parameters are required.');
}
*/

$connector = new Bitrix24Connector($input['connection'] ?? [], Application::getLog());

try {
    switch ($action) {
        case 'check':
            
            $response = $connector->check();
            break;

        case 'table_list':
            
            $response = $connector->tableList($input['searchString'] ?? '');
            break;

        case 'table_description':
            
            $response = $connector->tableDescription($input['table'] ?? '');
            break;

        case 'data':
            
            $response = $connector->getData(
                $input['table'] ?? '',
                $input['select'] ?? [],
                $input['filter'] ?? [],
                intval($input['limit']) === 0 ? 100 : intval($input['limit'])
            );
            break;

        default:
            $response = new Response('Unknown action', 400, ['Content-Type' => 'text/plain']);
            break;
    }
} catch (\Throwable $e) {
    $response = new Response(
        json_encode(['error' => $e->getMessage()]),
        500,
        ['Content-Type' => 'application/json']
    );
}

$response->send();