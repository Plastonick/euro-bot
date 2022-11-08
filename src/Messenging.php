<?php

namespace Plastonick\Euros;

use GuzzleHttp\Promise\PromiseInterface;

interface Messenging
{
    public function matchStarting(Game $match): PromiseInterface;

    public function matchComplete(Game $match): PromiseInterface;

    public function goalScored(Team $scoringTeam, Game $match): PromiseInterface;

    public function goalDisallowed(Game $match): PromiseInterface;
}
