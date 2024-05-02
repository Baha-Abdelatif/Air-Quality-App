<?php
require_once 'vendor/autoload.php';

$client = new \GuzzleHttp\Client(['verify' => false]);

$response = $client->request('GET', 'https://api.openaq.org/v2/latest?page=1&offset=0&sort=desc&radius=1000&order_by=lastUpdated&dump_raw=false', [
  'headers' => [
    'accept' => 'application/json',
    'content-type' => 'application/json',
  ],
]);

echo $response->getBody();
