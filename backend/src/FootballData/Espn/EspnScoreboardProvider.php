<?php

namespace Plastonick\Euros\FootballData\Espn;

use GuzzleHttp\ClientInterface;
use Plastonick\Euros\FootballData\FootballDataProvider;
use function json_decode;

final class EspnScoreboardProvider implements FootballDataProvider
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly EspnTeamMapper $teamMapper,
        private readonly EspnMatchMapper $matchMapper,
        private readonly string $scoreboardPath = '/apis/site/v2/sports/soccer/fifa.world/scoreboard'
    ) {
    }

    public function getTeams(string $competitionId): array
    {
        return $this->teamMapper->mapFromEvents($this->getEvents());
    }

    public function getMatches(string $competitionId, array $teams): array
    {
        $events = $this->getEvents();
        $currentTeams = $this->teamMapper->mapFromEvents($events);

        return $this->matchMapper->mapMany($events, $teams + $currentTeams);
    }

    private function getEvents(): array
    {
        $scoreboard = $this->getScoreboard();

        return $scoreboard['events'] ?? [];
    }

    private function getScoreboard(): array
    {
        $response = $this->client->request('GET', $this->scoreboardPath);

        return json_decode($response->getBody()->getContents(), true);
    }
}
