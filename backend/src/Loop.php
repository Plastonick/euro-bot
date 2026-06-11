<?php

namespace Plastonick\Euros;

use Psr\Log\LoggerInterface;
use Throwable;

class Loop
{
    public function __construct(
        private readonly StateBuilder $stateBuilder,
        private readonly MessengerCollection $messengerCollection,
        private readonly LoggerInterface $logger
    ) {
    }

    public function run(State $state): State
    {
        $this->logger->info('Updating match data');

        $updatedState = $this->stateBuilder->buildNewState($state->getTeams());
        $this->queueMessages($updatedState, $state);

        return $updatedState;
    }

    /**
     * @return void
     */
    public function dispatchQueuedMessages(): void
    {
        $messages = $this->messengerCollection->queue->retrieveReady();
        foreach ($messages as $message) {
            try {
                $this->logger->info('Sending message', ['content' => $message->content]);
                $message->notificationService->send($message->content)->wait();
            } catch (Throwable $throwable) {
                $this->logger->error('Failed waiting on promise', ['throwable' => $throwable]);
            }
        }
    }

    /**
     * @param State $updatedState
     * @param State $state
     *
     * @return void
     */
    private function queueMessages(State $updatedState, State $state): void
    {
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
                $this->messengerCollection->matchStarting($updatedMatch);
            }

            // Check for goals scored
            if ($updatedMatch->homeScore > $originalMatch->homeScore) {
                $this->logger->debug('Generating home team goal scored event');
                $this->messengerCollection->goalScored($updatedMatch->homeTeam, $updatedMatch);
            }

            if (
                $updatedMatch->homeScore < $originalMatch->homeScore
                || $updatedMatch->awayScore < $originalMatch->awayScore
            ) {
                $this->logger->debug('Generating goal disallowed event');
                $this->messengerCollection->goalDisallowed($updatedMatch);
            }

            if ($updatedMatch->awayScore > $originalMatch->awayScore) {
                $this->logger->debug('Generating away team goal scored event');
                $this->messengerCollection->goalScored($updatedMatch->awayTeam, $updatedMatch);
            }

            // Determine if the match has finished
            if ($originalMatch->status !== 'FINISHED' && $updatedMatch->status === 'FINISHED') {
                $this->logger->debug('Generating match completion event');
                $this->messengerCollection->matchComplete($updatedMatch);
            }
        }
    }
}
