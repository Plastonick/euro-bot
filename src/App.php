<?php

use Plastonick\Euros\Loop;
use Plastonick\Euros\Messager;
use Plastonick\Euros\StateBuilder;
use Plastonick\Euros\Team;
use Plastonick\Euros\Transport\SlackIncomingWebhook;

require __DIR__ . '/../vendor/autoload.php';
set_time_limit(0);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$competitionId = $_ENV['COMPETITION_ID'];

$apiClient = new \GuzzleHttp\Client(
    [
        'base_uri' => $_ENV['FOOTBALL_API_URI'],
        'timeout' => 0,
        'allow_redirects' => false,
        'headers' => ['X-Auth-Token' => $_ENV['API_KEY']],
    ]
);

$countryCodeMap = [
    'GER' => 'de',
    'ESP' => 'es',
    'POR' => 'pt',
    'SVK' => 'sk',
    'ENG' => 'england',
    'FRA' => 'fr',
    'DEN' => 'dk',
    'ITA' => 'it',
    'SUI' => 'ch',
    'UKR' => 'ua',
    'SWE' => 'se',
    'POL' => 'pl',
    'CZE' => 'cz',
    'CRO' => 'hr',
    'TUR' => 'tr',
    'BEL' => 'be',
    'RUS' => 'ru',
    'AUT' => 'at',
    'HUN' => 'hu',
    'WAL' => 'wales',
    'FIN' => 'fi',
    'MKD' => 'mk',
    'NED' => 'nl',
    'SCO' => 'scotland',
];

$teamsJson = $apiClient->get("competitions/{$competitionId}/teams");
$teamsArray = json_decode($teamsJson->getBody()->getContents(), true)['teams'];

$teams = [];
foreach ($teamsArray as $teamData) {
    $tla = $teamData['tla'];

    $id = $teamData['id'];
    $team = new Team($id, $teamData['name'], $countryCodeMap[$tla], $_ENV["TEAM_{$tla}"] ?? null);
    $teams[$id] = $team;
}

$stateBuilder = new StateBuilder($apiClient);
$state = $stateBuilder->buildNewState($teams);

$slackWebhookService = new SlackIncomingWebhook($_ENV['SLACK_WEB_HOOK']);
$messager = new Messager($slackWebhookService);
$loop = new Loop($stateBuilder, $messager);

while (true) {
    try {
        $state = $loop->run($state);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }

    sleep($state->getSleepLength());
}
