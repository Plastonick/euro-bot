<?php

namespace Plastonick\Euros;

use Plastonick\Euros\FootballData\FootballDataProvider;
use Psr\Log\LoggerInterface;

class StateBuilder
{
    public function __construct(
        private readonly FootballDataProvider $footballData,
        private readonly LoggerInterface $logger,
        private readonly string $competitionId
    ) {
    }

    public function buildNewState(array $teams): State
    {
        $matches = $this->footballData->getMatches($this->competitionId, $teams);

        return new State($teams, $matches, $this->logger);
    }
}
