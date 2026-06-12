<?php

namespace Plastonick\Test\Euros;

use PHPUnit\Framework\TestCase;
use Plastonick\Euros\FootballData\FootballDataOrg\FootballDataOrgMatchMapper;
use Plastonick\Euros\Team;
use Psr\Log\NullLogger;

class FootballDataOrgMatchMapperTest extends TestCase
{
    public function testMapsFootballDataOrgMatchToGame(): void
    {
        $teams = [
            1 => new Team(1, 'England', 'ENG'),
            2 => new Team(2, 'Scotland', 'SCO'),
        ];

        $fixture = new FootballDataOrgMatchMapper(new NullLogger());

        $game = $fixture->map(
            [
                'id' => 123,
                'status' => 'FINISHED',
                'utcDate' => '2024-06-14T19:00:00Z',
                'homeTeam' => ['id' => 1],
                'awayTeam' => ['id' => 2],
                'score' => [
                    'winner' => 'HOME_TEAM',
                    'fullTime' => [
                        'home' => 2,
                        'away' => 1,
                    ],
                ],
            ],
            $teams
        );

        self::assertNotNull($game);
        self::assertSame('123', $game->id);
        self::assertSame('FINISHED', $game->status);
        self::assertSame($teams[1], $game->homeTeam);
        self::assertSame($teams[2], $game->awayTeam);
        self::assertSame(2, $game->homeScore);
        self::assertSame(1, $game->awayScore);
        self::assertSame('HOME_TEAM', $game->winner);
    }

    public function testReturnsNullWhenMatchReferencesUnknownTeam(): void
    {
        $fixture = new FootballDataOrgMatchMapper(new NullLogger());

        $game = $fixture->map(
            [
                'id' => 123,
                'status' => 'SCHEDULED',
                'utcDate' => '2024-06-14T19:00:00Z',
                'homeTeam' => ['id' => 1],
                'awayTeam' => ['id' => 2],
                'score' => [
                    'winner' => null,
                    'fullTime' => [],
                ],
            ],
            [1 => new Team(1, 'England', 'ENG')]
        );

        self::assertNull($game);
    }
}
