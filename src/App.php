<?php

use GuzzleHttp\Client;
use Monolog\Logger;
use Plastonick\Euros\Configuration;
use Plastonick\Euros\ConfigurationService;
use Plastonick\Euros\ConfigurationServiceInterface;
use Plastonick\Euros\Loop;
use Plastonick\Euros\Messenger;
use Plastonick\Euros\MessengerCollection;
use Plastonick\Euros\NullConfigurationService;
use Plastonick\Euros\StateBuilder;
use Plastonick\Euros\Team;
use Plastonick\Euros\Transport\SlackIncomingWebhook;

require __DIR__ . '/../vendor/autoload.php';

set_time_limit(0);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
$stdout = new Monolog\Handler\StreamHandler('php://stdout');
$logger = new Logger('sweepstake_app', [$stdout]);

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
    $acronym = $teamData['tla'];
    if (isset($countryCodeMap[$acronym])) {
        $flag = $countryCodeMap[$acronym];
    } else {
        $logger->warning('Failed to retrieve flag name', ['acronym' => $acronym]);
        $flag = 'sc';
    }

    $id = $teamData['id'];
    $name = $teamData['name'];
    $team = new Team($id, $name, $acronym, $flag);
    $teams[$id] = $team;

    $logger->info('Registered team', ['id' => $id, 'name' => $name, 'flag' => $flag]);
}

$stateBuilder = new StateBuilder($apiClient, $logger);
$state = $stateBuilder->buildNewState($teams);

$webhookClient = new Client(
    [
        'timeout' => 3,
        'allow_redirects' => false,
        'headers' => [
            'user-agent' => 'PlastonickFootballSweepstakes',
        ],
    ]
);

$config = Configuration::fromEnv();
$slackWebhookService = new SlackIncomingWebhook($config->webHookUrl, $webhookClient, $logger);
$messenger = new Messenger($slackWebhookService, $config);
$messengerCollection = new MessengerCollection();
$messengerCollection->register($messenger);

if ($_ENV['DB_HOST']) {
    $connection = new \PDO(
        "pgsql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );

    $configurationService = new ConfigurationService($connection);
} else {
    $configurationService = new NullConfigurationService();
}


$loop = new Loop($stateBuilder, $messengerCollection, $logger);

$startOfTime = DateTime::createFromFormat('U', '0');

while (true) {
    // TODO handle deletions or just always retrieve all

    /** @var ConfigurationServiceInterface $configurationService */
    $newConfigurations = $configurationService->retrieveConfigurationsSince($startOfTime);

    foreach ($newConfigurations as $newConfiguration) {
        // TODO handle discord vs slack
        $slackWebhookService = new SlackIncomingWebhook($newConfiguration->webHookUrl, $webhookClient, $logger);
        $messenger = new Messenger($slackWebhookService, $newConfiguration);

        $messengerCollection->register($messenger);
    }

    try {
        $state = $loop->run($state);
    } catch (Exception $e) {
        $logger->error($e->getMessage());
    }

    sleep(max(1, $state->getSleepLength()));
}
