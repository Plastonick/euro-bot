<?php

use Plastonick\Euros\ApiError;
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
        $response->getBody()->write((string) new ApiError('Could not find existing configuration'));
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
        $response->getBody()->write(json_encode(['message' => 'Removed configuration if it existed']));
        return $response;
    } else {
        $response->getBody()->write((string) new ApiError('Failed to delete configuration'));
        return $response->withStatus(500);
    }

});
$app->put('/configuration', function (Request $request, Response $response, array $args) use ($configurationService) {
    $data = json_decode((string) $request->getBody(), true);
    $webhookUrl = trim($data['webhook']);

    $webhookUrl = filter_var($webhookUrl, FILTER_SANITIZE_URL);

    if (filter_var($webhookUrl, FILTER_VALIDATE_URL) === false) {
        $response->getBody()->write((string) new ApiError('Invalid webhook provided'));
        return $response->withStatus(400);
    }

    $service = Service::tryFrom($data['service']);
    if (!$service) {
        $validServiceNames = array_column(Service::cases(), 'name');
        $error = new ApiError('Invalid service type provided, valid: ' . implode(', ', $validServiceNames));
        $response->getBody()->write((string) $error);
        return $response->withStatus(400);
    }

    // The template might come through as an empty string, that's as good as null
    $unwrapTemplate = fn(?string $template): ?string => $template ?: null;

    $result = $configurationService->persistConfiguration(
        $webhookUrl,
        $service,
        $data['owners'],
        Emoji::createFromString($data['win']),
        Emoji::createFromString($data['score']),
        Emoji::createFromString($data['kickOff']),
        Emoji::createFromString($data['draw']),
        $unwrapTemplate($data['kickoffTemplate']),
        $unwrapTemplate($data['scoreTemplate']),
        $unwrapTemplate($data['disallowedTemplate']),
        $unwrapTemplate($data['wonTemplate']),
        $unwrapTemplate($data['drawnTemplate']),
    );

    if ($result) {
        $response->getBody()->write(json_encode(['message' => 'Successfully updated configuration']));
        return $response->withStatus(200);
    } else {
        $response->getBody()->write((string) new ApiError('Something went wrong'));
        return $response->withStatus(500);
    }
});
$app->post('/webhook-test', function (Request $request, Response $response, array $args) use ($configurationService) {
    $data = json_decode((string) $request->getBody(), true);

    $webhookUrl = trim($data['webhook']);
    $webhookUrl = filter_var($webhookUrl, FILTER_SANITIZE_URL);

    if (filter_var($webhookUrl, FILTER_VALIDATE_URL) === false) {
        $response->getBody()->write((string) new ApiError('Invalid webhook provided'));
        return $response->withStatus(400);
    }

    $service = Service::tryFrom($data['service']);
    if (!$service) {
        $validServiceNames = array_column(Service::cases(), 'name');
        $error = new ApiError('Invalid service type provided, valid: ' . implode(', ', $validServiceNames));
        $response->getBody()->write((string) $error);
        return $response->withStatus(400);
    }

    $result = $configurationService->persistTestWebhook($webhookUrl, $service);

    if ($result) {
        $response->getBody()->write(json_encode(['message' => 'Queued configuration test event']));
        return $response->withStatus(200);
    } else {
        $response->getBody()->write((string) new ApiError('Something went wrong'));
        return $response->withStatus(500);
    }
});

$app->run();
