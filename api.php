<?php
require_once 'vendor/autoload.php';

$client = new \GuzzleHttp\Client(['verify' => false]);

$response = $client->request('GET', 'https://api.openaq.org/v2/locations?country=fr', [
  'headers' => [
    'accept' => 'application/json',
    'content-type' => 'application/json',
  ],
]);

echo $response->getBody();
