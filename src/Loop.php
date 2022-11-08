<?php

namespace Plastonick\Euros;

use Psr\Log\LoggerInterface;

class Loop
{
    public function __construct(
        private StateBuilder $stateBuilder,
        private MessengerCollection $messager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function run(State $state): State
    {
        $this->logger->info('Updating match data');

        $updatedState = $this->stateBuilder->buildNewState($state->getTeams());
        $updatedMatches = $updatedState->getMatches();

        foreach ($state->getMatches() as $matchId => $originalMatch) {
            if (!isset($updatedMatches[$matchId])) {
                $this->logger->debug('Cannot find updated match', ['id' => $matchId]);

                continue;
            }

            $updatedMatch = $updatedMatches[$matchId];

            // Determine if the match has started since our last status
            if ($originalMatch->status === 'SCHEDULED' && $updatedMatch->status !== 'SCHEDULED') {
                $this->logger->debug('Generating match start event');
                $this->messager->matchStarting($updatedMatch);
            }

            // Check for goals scored
            if ($updatedMatch->homeScore > $originalMatch->homeScore) {
                $this->logger->debug('Generating home team goal scored event');
                $this->messager->goalScored($updatedMatch->homeTeam, $updatedMatch);
            }

            if ($updatedMatch->homeScore < $originalMatch->homeScore
                || $updatedMatch->awayScore < $originalMatch->awayScore
            ) {
                $this->logger->debug('Generating goal disallowed event');
                $this->messager->goalDisallowed($updatedMatch);
            }

            if ($updatedMatch->awayScore > $originalMatch->awayScore) {
                $this->logger->debug('Generating away team goal scored event');
                $this->messager->goalScored($updatedMatch->awayTeam, $updatedMatch);
            }

            // Determine if the match has finished
            if ($originalMatch->status !== 'FINISHED' && $updatedMatch->status === 'FINISHED') {
                $this->logger->debug('Generating match completion event');
                $this->messager->matchComplete($updatedMatch);
            }
        }

        return $updatedState;
    }
}
