<?php

namespace Plastonick\Euros\FootballData;

use Plastonick\Euros\Game;
use Plastonick\Euros\Team;

interface FootballDataProvider
{
    /**
     * @return Team[] keyed by provider team id
     */
    public function getTeams(string $competitionId): array;

    /**
     * @param Team[] $teams keyed by provider team id
     * @return Game[] keyed by provider match id
     */
    public function getMatches(string $competitionId, array $teams): array;
}
