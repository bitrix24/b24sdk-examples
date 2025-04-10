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
/*

// Testing cases when hanler returns wrong data to a widget

$result = new Response(json_encode([]), 200); 
$result->send();

die;
*/

$incomingRequest = Request::createFromGlobals();
Application::getLog()->debug('widget-handler.init', ['request' => $incomingRequest->request->all(), 'query' => $incomingRequest->query->all()]);

Application::processWidgetRequest($incomingRequest, $incomingRequest->query->get('widget'))->send();

// $answer = file_get_contents('widgets/problem-tasks/demo.json');

// echo $answer;