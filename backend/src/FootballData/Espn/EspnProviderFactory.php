<?php

namespace Plastonick\Euros\FootballData\Espn;

use Plastonick\Euros\FootballData\FootballDataProvider;
use Psr\Log\LoggerInterface;

final class EspnProviderFactory
{
    public static function createFromEnv(array $env, LoggerInterface $logger): FootballDataProvider
    {
        return new EspnScoreboardProvider(
            EspnScoreboardClientFactory::create($env['ESPN_API_URI'] ?? 'https://site.api.espn.com'),
            new EspnTeamMapper(),
            new EspnMatchMapper($logger),
            $env['ESPN_SCOREBOARD_PATH'] ?? '/apis/site/v2/sports/soccer/fifa.world/scoreboard'
        );
    }
}
