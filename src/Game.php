<?php

namespace Plastonick\Euros;

use DateTimeInterface;
use function in_array;

class Game
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $status,
        public readonly DateTimeInterface $startTime,
        public readonly Team $homeTeam,
        public readonly Team $awayTeam,
        public readonly ?int $homeScore,
        public readonly ?int $awayScore,
        public readonly ?string $winner
    ) {
    }

    public function inProgress(): bool
    {
        $inProgressStatuses = ['LIVE', 'IN_PLAY', 'PAUSED'];

        return in_array($this->status, $inProgressStatuses);
    }
}
