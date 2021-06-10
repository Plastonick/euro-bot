<?php

namespace Plastonick\Euros;

use function error_log;

class Loop
{
    private State $state;
    private StateBuilder $stateBuilder;
    private Slacker $slacker;

    public function __construct(State $state, StateBuilder $stateBuilder, Slacker $slacker)
    {
        $this->slacker = $slacker;
        $this->state = $state;
        $this->stateBuilder = $stateBuilder;
    }

    public function run()
    {
        $updatedState = $this->stateBuilder->buildNewState($this->state->getTeams());
        $updatedMatches = $updatedState->getMatches();

        foreach ($this->state->getMatches() as $matchId => $originalMatch) {
            if (!isset($updatedMatches[$matchId])) {
                error_log("Cannot find updated match for id {$matchId}");

                continue;
            }

            $updatedMatch = $updatedMatches[$matchId];

            // Determine if the match has started since our last status
            if ($originalMatch->status === 'SCHEDULED' && $updatedMatch->status !== 'SCHEDULED') {
                $this->slacker->matchStarting($updatedMatch);

                continue;
            }

            // Determine if the match has finished
            if ($originalMatch->status !== 'FINISHED' && $updatedMatch->status === 'FINISHED') {
                $this->slacker->matchComplete($updatedMatch);

                continue;
            }
        }

        $this->state = $updatedState;
    }
}
