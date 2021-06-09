<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$competitionId = 2018;

$client = new \GuzzleHttp\Client([
    'base_uri'        => 'https://api.football-data.org/v2/',
    'timeout'         => 0,
    'allow_redirects' => false,
]);

$matches = $client->get("competions/{$competitionId}/matches");
