<?php

namespace Plastonick\Euros\FootballData\Espn;

use Plastonick\Euros\FootballData\FootballDataProvider;
use Psr\Log\LoggerInterface;

final class EspnProviderFactory
{
    public static function create(LoggerInterface $logger): FootballDataProvider
    {
        return new EspnScoreboardProvider(
            EspnScoreboardClientFactory::create('https://site.api.espn.com'),
            new EspnTeamMapper(),
            new EspnMatchMapper($logger),
            '/apis/site/v2/sports/soccer/fifa.world/scoreboard',
            '/apis/site/v2/sports/soccer/fifa.world/teams'
        );
    }
}
