<?php

/**
 * This file is part of the b24sdk examples package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App;

use App\Controller\PlacementController;
use Bitrix24\SDK\Application\Requests\Placement\PlacementRequest;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// add to log all incoming requests
$logger = LoggerFactory::create();
$logger->debug('uf-type-handler.start', [
    'request' => $_REQUEST,
]);


$incomingRequest = Request::createFromGlobals();
$placementRequest = new PlacementRequest($incomingRequest);

//dump($placementRequest->getPlacementOptions());
//dump($placementRequest->getPlacementOptions()['ENTITY_VALUE_ID']);

$b24Sb = Bitrix24ServiceBuilderFactory::createFromPlacementRequest($incomingRequest);

$contact = $b24Sb->getCRMScope()->contact()->get((int)$placementRequest->getPlacementOptions()['ENTITY_VALUE_ID'])->contact();

dump($contact->getUserfieldByFieldName('UF_CRM_T2_FIELD'));
dump($_REQUEST);
