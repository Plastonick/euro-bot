<?php

namespace Plastonick\Euros;

enum Service: string
{
    case SLACK = 'slack';
    case DISCORD = 'discord';

    public function is(self $comparison): bool
    {
        return $this->name === $comparison->name;
    }
}
