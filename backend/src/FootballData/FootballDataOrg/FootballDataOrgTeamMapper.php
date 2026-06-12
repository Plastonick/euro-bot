<?php

namespace Plastonick\Euros\FootballData\FootballDataOrg;

use Plastonick\Euros\Team;

final class FootballDataOrgTeamMapper
{
    public function map(array $teamData): Team
    {
        return new Team(
            $teamData['id'],
            $teamData['name'],
            $teamData['tla']
        );
    }

    /**
     * @return Team[] keyed by football-data.org team id
     */
    public function mapMany(array $teamsData): array
    {
        $teams = [];
        foreach ($teamsData as $teamData) {
            $team = $this->map($teamData);
            $teams[$team->id] = $team;
        }

        return $teams;
    }
}
