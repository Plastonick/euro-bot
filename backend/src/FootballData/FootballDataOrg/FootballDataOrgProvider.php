<?php

namespace Plastonick\Euros\FootballData\FootballDataOrg;

use GuzzleHttp\ClientInterface;
use Plastonick\Euros\FootballData\FootballDataProvider;
use function json_decode;

final class FootballDataOrgProvider implements FootballDataProvider
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly FootballDataOrgTeamMapper $teamMapper,
        private readonly FootballDataOrgMatchMapper $matchMapper
    ) {
    }

    public function getTeams(string $competitionId): array
    {
        $response = $this->client->request('GET', "competitions/{$competitionId}/teams");
        $payload = json_decode($response->getBody()->getContents(), true);

        return $this->teamMapper->mapMany($payload['teams'] ?? []);
    }

    public function getMatches(string $competitionId, array $teams): array
    {
        $response = $this->client->request('GET', "competitions/{$competitionId}/matches");
        $payload = json_decode($response->getBody()->getContents(), true);

        return $this->matchMapper->mapMany($payload['matches'] ?? [], $teams);
    }
}
