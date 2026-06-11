<?php

namespace Plastonick\Euros;

use Psr\Log\LoggerInterface;

class State
{
    /**
     * @param Team[] $teams
     * @param Game[] $matches
     */
    public function __construct(
        private readonly array $teams,
        private readonly array $matches,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return Team[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    /**
     * @return Game[]
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    public function getSleepLength(): int
    {
        $normalDelay = (int) ($_ENV['UPDATE_DELAY'] ?? $_ENV['UPDATE_FREQUENCY'] ?? 600);
        $burstDelay = (int) ($_ENV['BURST_DELAY'] ?? $_ENV['UPDATE_FREQUENCY'] ?? 10);

        if ($this->hasMatchInProgress() || $this->isCloseToStartOfMatch(max($normalDelay, $burstDelay))) {
            $this->logger->debug("Using burst delay: {$burstDelay}s");

            return $burstDelay;
        } else {
            $this->logger->debug("Using normal delay: {$normalDelay}s");

            return $normalDelay;
        }
    }

    private function hasMatchInProgress(): bool
    {
        foreach ($this->matches as $match) {
            if ($match->inProgress()) {
                return true;
            }
        }

        return false;
    }

    private function isCloseToStartOfMatch(int $thresholdSeconds): bool
    {
        $now = time();

        foreach ($this->matches as $match) {
            // determine the proximity to the start of the match
            $timeDiff = abs($match->startTime->getTimestamp() - $now);

            if ($timeDiff < $thresholdSeconds) {
                return true;
            }
        }

        return false;
    }
}
