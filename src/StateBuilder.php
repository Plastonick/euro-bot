<?php

namespace Plastonick\Euros;

use GuzzleHttp\Client;
use function json_decode;

class StateBuilder
{
    private Client $footballApi;

    public function __construct(Client $footballApi)
    {
        $this->footballApi = $footballApi;
    }

    public function buildNewState(array $teams)
    {
        $matchesJson = $this->footballApi->get("competitions/{$_ENV['COMPETITION_ID']}/matches");

        $matchesDataArray = json_decode($matchesJson->getBody()->getContents(), true)['matches'];

        $matches = [];
        foreach ($matchesDataArray as $matchData) {
            $homeTeamId = $matchData['homeTeam']['id'];
            $awayTeamId = $matchData['awayTeam']['id'];
            $matchId = $matchData['id'];

            $matches[$matchId] = new Match(
                $matchData['status'],
                $teams[$homeTeamId] ?? null,
                $teams[$awayTeamId] ?? null,
                $matchData['score']['fullTime']['homeTeam'],
                $matchData['score']['fullTime']['awayTeam'],
                $matchData['score']['winner'],
            );
        }

        return new State($teams, $matches);
    }
}
