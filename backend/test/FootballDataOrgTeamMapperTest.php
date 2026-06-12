<?php

namespace Plastonick\Test\Euros;

use PHPUnit\Framework\TestCase;
use Plastonick\Euros\FootballData\FootballDataOrg\FootballDataOrgTeamMapper;

class FootballDataOrgTeamMapperTest extends TestCase
{
    public function testMapsFootballDataOrgTeamsById(): void
    {
        $fixture = new FootballDataOrgTeamMapper();

        $teams = $fixture->mapMany(
            [
                ['id' => 1, 'name' => 'England', 'tla' => 'ENG'],
                ['id' => 2, 'name' => 'Scotland', 'tla' => 'SCO'],
            ]
        );

        self::assertArrayHasKey(1, $teams);
        self::assertArrayHasKey(2, $teams);
        self::assertSame('England', $teams[1]->name);
        self::assertSame('SCO', $teams[2]->tla);
    }
}
