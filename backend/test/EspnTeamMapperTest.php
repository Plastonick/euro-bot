<?php

namespace Plastonick\Test\Euros;

use PHPUnit\Framework\TestCase;
use Plastonick\Euros\FootballData\Espn\EspnTeamMapper;

class EspnTeamMapperTest extends TestCase
{
    public function testMapsTeamsFromScoreboardEventsById(): void
    {
        $fixture = new EspnTeamMapper();

        $teams = $fixture->mapFromEvents(
            [
                [
                    'competitions' => [
                        [
                            'competitors' => [
                                [
                                    'team' => [
                                        'id' => '203',
                                        'displayName' => 'Mexico',
                                        'abbreviation' => 'MEX',
                                    ],
                                ],
                                [
                                    'team' => [
                                        'id' => '467',
                                        'displayName' => 'South Africa',
                                        'abbreviation' => 'RSA',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        self::assertArrayHasKey(203, $teams);
        self::assertArrayHasKey(467, $teams);
        self::assertSame('Mexico', $teams[203]->name);
        self::assertSame('RSA', $teams[467]->tla);
    }
}
