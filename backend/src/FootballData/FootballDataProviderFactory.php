<?php

namespace Plastonick\Euros\FootballData;

use Plastonick\Euros\FootballData\Espn\EspnProviderFactory;
use Plastonick\Euros\FootballData\FootballDataOrg\FootballDataOrgProviderFactory;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class FootballDataProviderFactory
{
    public static function createFromEnv(array $env, LoggerInterface $logger): FootballDataProvider
    {
        return match ($env['FOOTBALL_DATA_PROVIDER'] ?? 'football-data.org') {
            'football-data.org' => FootballDataOrgProviderFactory::createFromEnv($env, $logger),
            'espn' => EspnProviderFactory::create($logger),
            default => throw new RuntimeException('Unsupported football data provider'),
        };
    }
}
