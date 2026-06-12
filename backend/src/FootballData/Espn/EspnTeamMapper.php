<?php

namespace Plastonick\Euros\FootballData\Espn;

use Plastonick\Euros\Team;

final class EspnTeamMapper
{
    public function map(array $competitor): Team
    {
        $team = $competitor['team'];

        return new Team(
            (int) $team['id'],
            $team['displayName'] ?? $team['name'],
            $team['abbreviation']
        );
    }

    /**
     * @return Team[] keyed by ESPN team id
     */
    public function mapFromEvents(array $events): array
    {
        $teams = [];
        foreach ($events as $event) {
            foreach ($this->getCompetitors($event) as $competitor) {
                $team = $this->map($competitor);
                $teams[$team->id] = $team;
            }
        }

        return $teams;
    }

    private function getCompetitors(array $event): array
    {
        $competition = $event['competitions'][0] ?? null;

        return $competition['competitors'] ?? [];
    }
}
