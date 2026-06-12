<?php

namespace Plastonick\Test\Euros;

use PHPUnit\Framework\TestCase;
use Plastonick\Euros\FootballData\FootballDataProvider;
use Plastonick\Euros\Game;
use Plastonick\Euros\StateBuilder;
use Plastonick\Euros\Team;
use Psr\Log\NullLogger;

class StateBuilderTest extends TestCase
{
    public function testBuildsStateFromFootballDataProvider(): void
    {
        $teams = [1 => new Team(1, 'England', 'ENG')];
        $matches = [
            123 => new Game(
                123,
                'SCHEDULED',
                new \DateTimeImmutable('2024-06-14T19:00:00Z'),
                $teams[1],
                $teams[1],
                null,
                null,
                null
            ),
        ];

        $provider = new class ($matches) implements FootballDataProvider {
            public function __construct(private readonly array $matches)
            {
            }

            public function getTeams(string $competitionId): array
            {
                return [];
            }

            public function getMatches(string $competitionId, array $teams): array
            {
                return $this->matches;
            }
        };

        $fixture = new StateBuilder($provider, new NullLogger(), '2000');

        $state = $fixture->buildNewState($teams);

        self::assertSame($teams, $state->getTeams());
        self::assertSame($matches, $state->getMatches());
    }
}
