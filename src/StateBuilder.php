<?php

namespace Plastonick\Euros;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use function json_decode;

class StateBuilder
{
    public function __construct(private readonly Client $footballApi, private readonly LoggerInterface $logger)
    {
    }

    public function buildNewState(array $teams): State
    {
        $matchesJson = $this->footballApi->get("competitions/{$_ENV['COMPETITION_ID']}/matches");
        $matchesDataArray = json_decode($matchesJson->getBody()->getContents(), true)['matches'];

        $matches = [];
        foreach ($matchesDataArray as $matchData) {
            $game = $this->buildGame($matchData, $teams);
            if ($game) {
                $matches[$game->id] = $game;
            }
        }

        return new State($teams, $matches, $this->logger);
    }

    private function buildGame(array $matchData, array $teams): ?Game
    {
        $homeTeamId = $matchData['homeTeam']['id'];
        $awayTeamId = $matchData['awayTeam']['id'];
        $matchId = $matchData['id'];

        try {
            $startTime = new DateTime($matchData['utcDate']);
        } catch (Exception $e) {
            $this->logger->error(
                'Failed to build start time',
                ['utcDate' => $matchData['utcDate'] ?? null, 'error' => $e->getMessage()]
            );

            return null;
        }

        $homeTeam = $teams[$homeTeamId] ?? null;
        $awayTeam = $teams[$awayTeamId] ?? null;

        if (!$homeTeam || !$awayTeam) {
            return null;
        }

        return new Game(
            $matchId,
            $matchData['status'],
            $startTime,
            $homeTeam,
            $awayTeam,
            $matchData['score']['fullTime']['homeTeam'],
            $matchData['score']['fullTime']['awayTeam'],
            $matchData['score']['winner'],
        );
    }
}
