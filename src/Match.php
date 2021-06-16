<?php

namespace Plastonick\Euros;

use DateTimeInterface;
use function in_array;

class Match
{
    public string $status;
    public DateTimeInterface $startTime;
    public ?Team $homeTeam;
    public ?Team $awayTeam;
    public ?int $homeScore;
    public ?int $awayScore;
    public ?string $winner;

    public function __construct(
        string $status,
        DateTimeInterface $startTime,
        ?Team $homeTeam,
        ?Team $awayTeam,
        ?int $homeScore,
        ?int $awayScore,
        ?string $winner
    ) {
        $this->status = $status;
        $this->startTime = $startTime;
        $this->homeTeam = $homeTeam;
        $this->awayTeam = $awayTeam;
        $this->homeScore = $homeScore;
        $this->awayScore = $awayScore;
        $this->winner = $winner;
    }

    public function inProgress(): bool
    {
        $inProgressStatuses = ['LIVE', 'IN_PLAY', 'PAUSED'];

        return in_array($this->status, $inProgressStatuses);
    }
}
