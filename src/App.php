<?php

use GuzzleHttp\Client;
use Monolog\Logger;
use Plastonick\Euros\Configuration;
use Plastonick\Euros\ConfigurationService;
use Plastonick\Euros\ConfigurationServiceInterface;
use Plastonick\Euros\CountryCode;
use Plastonick\Euros\Loop;
use Plastonick\Euros\Messenger;
use Plastonick\Euros\MessengerCollection;
use Plastonick\Euros\StaticConfigurationService;
use Plastonick\Euros\StateBuilder;
use Plastonick\Euros\Team;

$stdout = new Monolog\Handler\StreamHandler('php://stdout');
$logger = new Logger('sweepstake_app', [$stdout]);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
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

$teamsJson = $apiClient->get("competitions/{$competitionId}/teams");
$teamsArray = json_decode($teamsJson->getBody()->getContents(), true)['teams'];

$teams = [];
foreach ($teamsArray as $teamData) {
    $country = CountryCode::from($teamData['tla']);

    $id = $teamData['id'];
    $name = $teamData['name'];
    $team = new Team($id, $name, $country, $country->getFlagCode());
    $teams[$id] = $team;

    $logger->info('Registered team', ['id' => $id, 'name' => $name, 'tla' => $country->name]);
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

$messengerCollection = new MessengerCollection();

if ($_ENV['DB_HOST']) {
    $connection = new \PDO(
        "pgsql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );

    $logger->info('Found database credentials');
    $configurationService = new ConfigurationService($connection);
} else {
    $config = Configuration::fromEnv();
    $messenger = new Messenger($webhookClient, $config);
    $messengerCollection->register($messenger);

    $logger->debug('No database credentials, using environment credentials');
    $configurationService = new StaticConfigurationService($config);
}


$loop = new Loop($stateBuilder, $messengerCollection, $logger);

$startOfTime = DateTime::createFromFormat('U', '0');

while (true) {
    // TODO handle deletions or just accept always retrieving all

    /** @var ConfigurationServiceInterface $configurationService */
    $newConfigurations = $configurationService->retrieveConfigurationsSince($startOfTime);
    $messengerCollection->clear();

    foreach ($newConfigurations as $newConfiguration) {
        $messenger = new Messenger($webhookClient, $newConfiguration);

        $messengerCollection->register($messenger);
    }

    try {
        $state = $loop->run($state);
    } catch (Exception $e) {
        $logger->error($e->getMessage());
    }

    sleep(max(1, $state->getSleepLength()));
}
