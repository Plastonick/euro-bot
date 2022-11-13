<?php

use Plastonick\Euros\ConfigurationService;
use Plastonick\Euros\Emoji;
use Plastonick\Euros\Service;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

header('Access-Control-Allow-Origin: *');

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

$app->addBodyParsingMiddleware();

$app->add(function (Request $request, RequestHandler $handler): Response {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'PUT,GET,DELETE,OPTIONS');
});

$app->addRoutingMiddleware();

$app->options('/{routes:.+}', function (Request $request, Response $response): Response {
    return $response;
});

$app->get('/configuration', function (Request $request, Response $response, array $args) use ($configurationService) {
    $url = $request->getQueryParams()['url'] ?? null;

    if (!$url) {
        return $response->withStatus(404);
    }

    $configuration = $configurationService->retrieveConfiguration(trim($url));

    if ($configuration) {
        $response->getBody()->write(json_encode($configuration->toArray()));
        return $response;
    } else {
        $response->getBody()->write('Could not find configuration');
        return $response->withStatus(404);
    }

});
$app->delete('/configuration', function (Request $request, Response $response, array $args) use ($configurationService) {
    $url = $request->getQueryParams()['url'] ?? null;

    if (!$url) {
        return $response->withStatus(404);
    }

    $result = $configurationService->deleteConfiguration(trim($url));

    if ($result) {
        $response->getBody()->write('Removed configuration if it existed');
        return $response;
    } else {
        $response->getBody()->write('Failed to delete configuration');
        return $response->withStatus(500);
    }

});
$app->put('/configuration', function (Request $request, Response $response, array $args) use ($configurationService) {
    $data = json_decode((string) $request->getBody(), true);
    $webhookUrl = trim($data['webhook']);

    $webhookUrl = filter_var($webhookUrl, FILTER_SANITIZE_URL);

    if (filter_var($webhookUrl, FILTER_VALIDATE_URL) === false) {
        $response->getBody()->write('Invalid URL provided');
        return $response->withStatus(400);
    }

    $service = Service::tryFrom($data['service']);
    if (!$service) {
        $validServiceNames = array_column(Service::cases(), 'name');
        $response->getBody()->write('Invalid service type provided, valid: ' . implode(', ', $validServiceNames));
        return $response->withStatus(400);
    }

    $result = $configurationService->persistConfiguration(
        $webhookUrl,
        $service,
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
