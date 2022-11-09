<?php

use Plastonick\Euros\ConfigurationService;
use Plastonick\Euros\Emoji;
use Plastonick\Euros\Service;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$dbHost = $_ENV['DB_HOST'] ?? null;
$dbPort = $_ENV['DB_PORT'] ?? null;
$dbName = $_ENV['DB_NAME'] ?? null;
$dbUser = $_ENV['DB_USER'] ?? null;
$dbPass = $_ENV['DB_PASS'] ?? null;

if (!$dbHost) {
    return;
}

$connection = new \PDO(
    "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName}",
    $dbUser,
    $dbPass
);

$configurationService = new ConfigurationService($connection);

$app->get('/configuration', function (Request $request, Response $response, array $args) use ($configurationService) {
    $configuration = $configurationService->retrieveConfiguration(trim($request->getQueryParams()['url']));

    if ($configuration) {
        $response->getBody()->write(json_encode($configuration->toArray()));
        return $response;
    } else {
        $response->getBody()->write('Could not find configuration');
        return $response->withStatus(404);
    }

});
$app->delete('/configuration', function (Request $request, Response $response, array $args) use ($configurationService) {
    $result = $configurationService->deleteConfiguration(trim($request->getQueryParams()['url']));

    if ($result) {
        $response->getBody()->write('Removed configuration if it existed');
        return $response;
    } else {
        $response->getBody()->write('Failed to delete configuration');
        return $response->withStatus(404);
    }

});
$app->put('/configuration', function (Request $request, Response $response, array $args) use ($configurationService) {
    $data = json_decode($request->getBody()->getContents(), true);
    $webhookUrl = trim($data['webhook']);

    $webhookUrl = filter_var($webhookUrl, FILTER_SANITIZE_URL);

    if (filter_var($webhookUrl, FILTER_VALIDATE_URL) === false) {
        $response->getBody()->write('Invalid URL provided');
        return $response->withStatus(400);
    }

    $result = $configurationService->persistConfiguration(
        $webhookUrl,
        Service::from($data['service']),
        $data['owners'],
        Emoji::createFromString($data['win']),
        Emoji::createFromString($data['score']),
        Emoji::createFromString($data['kickOff']),
        Emoji::createFromString($data['draw']),
    );

    if ($result) {
        return $response->withStatus(200);
    } else {
        $response->getBody()->write('Something went wrong');
        return $response->withStatus(500);
    }
});

$app->run();
