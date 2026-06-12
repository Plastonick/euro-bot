<?php

namespace Plastonick\Euros\FootballData\Espn;

use DateTime;
use Exception;
use Plastonick\Euros\Game;
use Psr\Log\LoggerInterface;

final class EspnMatchMapper
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function map(array $event, array $teams): ?Game
    {
        $competition = $event['competitions'][0] ?? null;
        if (!$competition) {
            return null;
        }

        $homeCompetitor = $this->findCompetitor($competition, 'home');
        $awayCompetitor = $this->findCompetitor($competition, 'away');
        if (!$homeCompetitor || !$awayCompetitor) {
            return null;
        }

        $homeTeam = $teams[(int) $homeCompetitor['team']['id']] ?? null;
        $awayTeam = $teams[(int) $awayCompetitor['team']['id']] ?? null;
        if (!$homeTeam || !$awayTeam) {
            return null;
        }

        try {
            $startTime = new DateTime($competition['startDate'] ?? $competition['date'] ?? $event['date']);
        } catch (Exception $e) {
            $this->logger->error(
                'Failed to build start time',
                ['date' => $competition['startDate'] ?? $competition['date'] ?? $event['date'] ?? null, 'error' => $e->getMessage()]
            );

            return null;
        }

        return new Game(
            $event['id'],
            $this->mapStatus($competition['status'] ?? $event['status'] ?? []),
            $startTime,
            $homeTeam,
            $awayTeam,
            $this->mapScore($homeCompetitor),
            $this->mapScore($awayCompetitor),
            $this->mapWinner($competition, $homeCompetitor, $awayCompetitor),
        );
    }

    /**
     * @return Game[] keyed by ESPN event id
     */
    public function mapMany(array $events, array $teams): array
    {
        $matches = [];
        foreach ($events as $event) {
            $game = $this->map($event, $teams);
            if ($game) {
                $matches[$game->id] = $game;
            }
        }

        return $matches;
    }

    private function findCompetitor(array $competition, string $homeAway): ?array
    {
        foreach ($competition['competitors'] ?? [] as $competitor) {
            if (($competitor['homeAway'] ?? null) === $homeAway) {
                return $competitor;
            }
        }

        return null;
    }

    private function mapStatus(array $status): ?string
    {
        $type = $status['type'] ?? [];

        if (($type['completed'] ?? false) === true) {
            return 'FINISHED';
        }

        return match ($type['state'] ?? null) {
            'pre' => 'SCHEDULED',
            'in' => ($type['name'] ?? null) === 'STATUS_HALFTIME' ? 'PAUSED' : 'IN_PLAY',
            'post' => 'FINISHED',
            default => $type['name'] ?? null,
        };
    }

    private function mapScore(array $competitor): ?int
    {
        if (!isset($competitor['score'])) {
            return null;
        }

        return (int) $competitor['score'];
    }

    private function mapWinner(array $competition, array $homeCompetitor, array $awayCompetitor): ?string
    {
        $status = $competition['status']['type'] ?? [];
        if (($status['completed'] ?? false) !== true) {
            return null;
        }

        if (($homeCompetitor['winner'] ?? false) === true) {
            return 'HOME_TEAM';
        }

        if (($awayCompetitor['winner'] ?? false) === true) {
            return 'AWAY_TEAM';
        }

        if ($this->mapScore($homeCompetitor) === $this->mapScore($awayCompetitor)) {
            return 'DRAW';
        }

        return null;
    }
}
