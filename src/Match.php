<?php

namespace Plastonick\Euros;

class Match
{
    public string $status;
    public ?Team $homeTeam;
    public ?Team $awayTeam;
    public ?int $homeScore;
    public ?int $awayScore;
    public ?string $winner;

    public function __construct(
        string $status,
        ?Team $homeTeam,
        ?Team $awayTeam,
        ?int $homeScore,
        ?int $awayScore,
        ?string $winner
    ) {
        $this->status = $status;
        $this->homeTeam = $homeTeam;
        $this->awayTeam = $awayTeam;
        $this->homeScore = $homeScore;
        $this->awayScore = $awayScore;
        $this->winner = $winner;
    }
}
