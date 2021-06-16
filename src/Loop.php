<?php

namespace Plastonick\Euros;

use function date;
use const DATE_ATOM;
use function error_log;

class Loop
{
    private StateBuilder $stateBuilder;
    private Messager $messager;

    public function __construct(StateBuilder $stateBuilder, Messager $messager)
    {
        $this->messager = $messager;
        $this->stateBuilder = $stateBuilder;
    }

    public function run(State $state): State
    {
        echo date(DATE_ATOM) . " - Updating match data\n";

        $updatedState = $this->stateBuilder->buildNewState($state->getTeams());
        $updatedMatches = $updatedState->getMatches();

        foreach ($state->getMatches() as $matchId => $originalMatch) {
            if (!isset($updatedMatches[$matchId])) {
                error_log("Cannot find updated match for id {$matchId}");

                continue;
            }

            $updatedMatch = $updatedMatches[$matchId];

            // Determine if the match has started since our last status
            if ($originalMatch->status === 'SCHEDULED' && $updatedMatch->status !== 'SCHEDULED') {
                $this->messager->matchStarting($updatedMatch);
                echo date(DATE_ATOM) . " - Generating match start event\n";
            }

            // Check for goals scored
            if ($updatedMatch->homeScore > $originalMatch->homeScore) {
                $this->messager->goalScored($updatedMatch->homeTeam, $updatedMatch);
                echo date(DATE_ATOM) . " - Generating home team goal scored event\n";
            }

            if ($updatedMatch->homeScore < $originalMatch->homeScore
                || $updatedMatch->awayScore < $originalMatch->awayScore
            ) {
                $this->messager->goalDisallowed($updatedMatch);
                echo date(DATE_ATOM) . " - Generating goal disallowed event\n";
            }

            if ($updatedMatch->awayScore > $originalMatch->awayScore) {
                $this->messager->goalScored($updatedMatch->awayTeam, $updatedMatch);
                echo date(DATE_ATOM) . " - Generating away team goal scored event\n";
            }

            // Determine if the match has finished
            if ($originalMatch->status !== 'FINISHED' && $updatedMatch->status === 'FINISHED') {
                $this->messager->matchComplete($updatedMatch);
                echo date(DATE_ATOM) . " - Generating match completion event\n";
            }
        }

        return $updatedState;
    }
}
