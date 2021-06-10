<?php

namespace Plastonick\Euros;

class State
{
    /**
     * @var Team[]
     */
    private array $teams;

    /**
     * @var Match[]
     */
    private array $matches;

    public function __construct(array $teams, array $matches)
    {
        $this->teams = $teams;
        $this->matches = $matches;
    }

    /**
     * @return Team[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    /**
     * @return Match[]
     */
    public function getMatches(): array
    {
        return $this->matches;
    }
}
