<?php

namespace Plastonick\Euros;

class Team
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $tla
    ) {
    }

    public function getFlagEmoji(): string
    {
        return Flag::from($this->tla);
    }
}
