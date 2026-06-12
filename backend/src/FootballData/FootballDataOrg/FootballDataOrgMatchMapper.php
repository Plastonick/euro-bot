<?php

namespace Plastonick\Euros\FootballData\FootballDataOrg;

use DateTime;
use Exception;
use Plastonick\Euros\Game;
use Psr\Log\LoggerInterface;

final class FootballDataOrgMatchMapper
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function map(array $matchData, array $teams): ?Game
    {
        $homeTeamId = $matchData['homeTeam']['id'];
        $awayTeamId = $matchData['awayTeam']['id'];
        $fullTimeScore = $matchData['score']['fullTime'] ?? [];

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
            $matchData['id'],
            $matchData['status'],
            $startTime,
            $homeTeam,
            $awayTeam,
            $fullTimeScore['homeTeam'] ?? $fullTimeScore['home'] ?? null,
            $fullTimeScore['awayTeam'] ?? $fullTimeScore['away'] ?? null,
            $matchData['score']['winner'] ?? null,
        );
    }

    /**
     * @return Game[] keyed by football-data.org match id
     */
    public function mapMany(array $matchesData, array $teams): array
    {
        $matches = [];
        foreach ($matchesData as $matchData) {
            $game = $this->map($matchData, $teams);
            if ($game) {
                $matches[$game->id] = $game;
            }
        }

        return $matches;
    }
}
