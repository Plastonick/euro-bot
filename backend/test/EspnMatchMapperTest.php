<?php

namespace Plastonick\Test\Euros;

use PHPUnit\Framework\TestCase;
use Plastonick\Euros\FootballData\Espn\EspnMatchMapper;
use Plastonick\Euros\Team;
use Psr\Log\NullLogger;

class EspnMatchMapperTest extends TestCase
{
    public function testMapsScoreboardEventToGame(): void
    {
        $teams = [
            203 => new Team(203, 'Mexico', 'MEX'),
            467 => new Team(467, 'South Africa', 'RSA'),
        ];

        $fixture = new EspnMatchMapper(new NullLogger());

        $game = $fixture->map($this->event(), $teams);

        self::assertNotNull($game);
        self::assertSame('760415', $game->id);
        self::assertSame('FINISHED', $game->status);
        self::assertSame($teams[203], $game->homeTeam);
        self::assertSame($teams[467], $game->awayTeam);
        self::assertSame(2, $game->homeScore);
        self::assertSame(0, $game->awayScore);
        self::assertSame('HOME_TEAM', $game->winner);
    }

    public function testMapsInProgressStatus(): void
    {
        $event = $this->event();
        $event['competitions'][0]['status']['type'] = [
            'name' => 'STATUS_IN_PROGRESS',
            'state' => 'in',
            'completed' => false,
        ];

        $teams = [
            203 => new Team(203, 'Mexico', 'MEX'),
            467 => new Team(467, 'South Africa', 'RSA'),
        ];

        $game = (new EspnMatchMapper(new NullLogger()))->map($event, $teams);

        self::assertSame('IN_PLAY', $game->status);
        self::assertNull($game->winner);
    }

    public function testReturnsNullWhenEventReferencesUnknownTeam(): void
    {
        $game = (new EspnMatchMapper(new NullLogger()))->map(
            $this->event(),
            [203 => new Team(203, 'Mexico', 'MEX')]
        );

        self::assertNull($game);
    }

    private function event(): array
    {
        return [
            'id' => '760415',
            'date' => '2026-06-11T19:00Z',
            'competitions' => [
                [
                    'startDate' => '2026-06-11T19:00Z',
                    'status' => [
                        'type' => [
                            'name' => 'STATUS_FULL_TIME',
                            'state' => 'post',
                            'completed' => true,
                        ],
                    ],
                    'competitors' => [
                        [
                            'homeAway' => 'home',
                            'winner' => true,
                            'score' => '2',
                            'team' => [
                                'id' => '203',
                                'displayName' => 'Mexico',
                                'abbreviation' => 'MEX',
                            ],
                        ],
                        [
                            'homeAway' => 'away',
                            'winner' => false,
                            'score' => '0',
                            'team' => [
                                'id' => '467',
                                'displayName' => 'South Africa',
                                'abbreviation' => 'RSA',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
