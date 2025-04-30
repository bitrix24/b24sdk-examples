<?php

declare(strict_types=1);

use Bitrix24\SDK\Services\ServiceBuilderFactory;

require_once 'vendor/autoload.php';

$B24 = ServiceBuilderFactory::createServiceBuilderFromWebhook(
    'PUT-YOUR-WEBHOOK-URL-HERE'
);

print_r($B24->core->call('user.current')->getResponseData()->getResult());

/* Autocomplete demo

$deals = $B24->getCRMScope()->deal()->list([], [], ['TITLE', 'OPPORTUNITY', 'BEGINDATE'])->getDeals();

foreach ($deals as $deal) {
    echo '<p>title: '.$deal->TITLE.'<br/>';
    echo 'day of week: '.$deal->BEGINDATE->dayOfWeek().'<br/>';
    echo 'amount: '.$deal->OPPORTUNITY.'</p>';
}

*/

/* Batch demo

foreach ($B24->getCRMScope()->deal()->batch->list([], [], ['TITLE', 'OPPORTUNITY', 'BEGINDATE'], 10000) 
    as $deal) {
    echo '<p>title: '.$deal->TITLE.'<br/>';
    echo 'day of week: '.$deal->BEGINDATE->dayOfWeek().'<br/>';
    echo 'amount: '.$deal->OPPORTUNITY.'</p>';
}

*/