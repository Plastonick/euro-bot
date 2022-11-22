<?php

namespace Plastonick\Euros;

interface Messenging
{
    public function matchStarting(Game $match): Message;

    public function matchComplete(Game $match): Message;

    public function goalScored(Team $scoringTeam, Game $match): Message;

    public function goalDisallowed(Game $match): Message;
}
