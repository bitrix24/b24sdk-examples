<?php

/**
 * This file is part of the b24sdk-examples package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

// In this example we work with cURL without bitrix24-php-sdk
// At this level of abstraction, you will have to handle errors that will occur on your own.
// These are errors when working with the network, errors when working with Bitrix24

print('Show all env variables:');
print_r($_ENV);
print('=====================' . PHP_EOL);

// Define incoming webhook from
$bitrix24WebhookURL = $_ENV['BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL'];

// we call method crm.lead.add that's why we need add scope CRM in webhook settings
$method = 'crm.lead.add';
$postFields = [
    'fields' => [
        'TITLE' => 'New Lead from cURL',
        'NAME' => 'John',
        'LAST_NAME' => 'Doe',
        'STATUS_ID' => 'NEW',
        'OPENED' => 'Y',
        'ASSIGNED_BY_ID' => 1,
        'PHONE' => [
            ['VALUE' => '+1234567890', 'VALUE_TYPE' => 'WORK']
        ],
        'EMAIL' => [
            ['VALUE' => 'test@example.com', 'VALUE_TYPE' => 'WORK']
        ]
    ],
    'params' => ['REGISTER_SONET_EVENT' => 'Y']
];

// Initialize cURL
if (!extension_loaded('curl')) {
    print("fatal error: cURL is not available. Activete it or install it." . PHP_EOL);
    exit();
}

$curl = curl_init();

// Set cURL options
curl_setopt_array($curl, [
    CURLOPT_URL => $bitrix24WebhookURL . $method,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postFields), // Convert data to URL-encoded query
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded'
    ]
]);

// Execute request and fetch response
$response = curl_exec($curl);

// Check for errors
if (curl_errno($curl)) {
    echo 'Error: ' . curl_error($curl);
} else {
    // Decode response
    $responseData = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    print_r($responseData);
}

// Close cURL session
curl_close($curl);