<?php

namespace Plastonick\Euros;

use function date;
use const DATE_ATOM;

class State
{
    /**
     * @var Team[]
     */
    private array $teams;

    /**
     * @var Match[]
     */
    private array $matches;

    public function __construct(array $teams, array $matches)
    {
        $this->teams = $teams;
        $this->matches = $matches;
    }

    /**
     * @return Team[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    /**
     * @return Match[]
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    public function getSleepLength(): int
    {
        $normalDelay = (int) ($_ENV['UPDATE_DELAY'] ?? $_ENV['UPDATE_FREQUENCY'] ?? 600);
        $burstDelay = (int) ($_ENV['BURST_DELAY'] ?? $_ENV['UPDATE_FREQUENCY'] ?? 10);

        if ($this->hasMatchInProgress() || $this->isCloseToStartOfMatch($normalDelay)) {
            echo date(DATE_ATOM) . " - Using burst delay: {$burstDelay}s\n";

            return $burstDelay;
        } else {
            echo date(DATE_ATOM) . " - Using normal delay: {$normalDelay}s\n";

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

    private function isCloseToStartOfMatch(int $thresholdSeconds = 600): bool
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
