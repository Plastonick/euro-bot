<?php

namespace Plastonick\Euros;

class Team
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $acronym,
        public readonly string $flagCode
    ) {
    }

    public function getFlagEmoji(): string
    {
        return ":flag-{$this->flagCode}:";
    }
}
