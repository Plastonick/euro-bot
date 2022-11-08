<?php

namespace Plastonick\Euros;

interface Messenging
{
    public function matchStarting(Game $match): void;

    public function matchComplete(Game $match): void;

    public function goalScored(Team $scoringTeam, Game $match): void;

    public function goalDisallowed(Game $match): void;
}
