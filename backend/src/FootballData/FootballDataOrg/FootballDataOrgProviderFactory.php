<?php

namespace Plastonick\Euros\FootballData\FootballDataOrg;

use Plastonick\Euros\FootballData\FootballDataProvider;
use Psr\Log\LoggerInterface;

final class FootballDataOrgProviderFactory
{
    public static function createFromEnv(array $env, LoggerInterface $logger): FootballDataProvider
    {
        return new FootballDataOrgProvider(
            FootballDataOrgClientFactory::create($env['FOOTBALL_API_URI'], $env['API_KEY']),
            new FootballDataOrgTeamMapper(),
            new FootballDataOrgMatchMapper($logger)
        );
    }
}
