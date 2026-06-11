<?php

namespace Plastonick\Euros;

use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Log\LoggerInterface;
use function count;

class MessengerCollection
{
    /**
     * @var Messenger[]
     */
    private array $messengers = [];

    public function __construct(public readonly MessageQueue $queue, private readonly LoggerInterface $logger)
    {
    }

    public function register(Messenger $messager): self
    {
        $this->messengers[$messager->config->webHookUrl] = $messager;

        return $this;
    }

    public function deregister(string $webhookUrl): self
    {
        unset($this->messengers[$webhookUrl]);

        return $this;
    }

    public function clear(): self
    {
        $this->messengers = [];

        return $this;
    }

    public function matchStarting(Game $match): void
    {
        foreach ($this->messengers as $messenger) {
            $this->queue->add($messenger->matchStarting($match));
        }
    }

    public function matchComplete(Game $match): void
    {
        foreach ($this->messengers as $messenger) {
            $this->queue->add($messenger->matchComplete($match));
        }
    }

    public function goalScored(Team $scoringTeam, Game $match): void
    {
        foreach ($this->messengers as $messenger) {
            $this->queue->add($messenger->goalScored($scoringTeam, $match));
        }
    }

    public function goalDisallowed(Game $match): void
    {
        foreach ($this->messengers as $messenger) {
            $this->queue->add($messenger->goalDisallowed($match));
        }
    }

    /**
     * @param PromiseInterface[] $promises
     *
     * @return void
     */
    private function dispatchPromises(array $promises): void
    {
        $requests = function () use ($promises): Generator {
            for ($i = 0; $i < count($promises); $i++) {
                $promise = $promises[$i];
                yield function() use ($promise): PromiseInterface {
                    return $promise;
                };
            }
        };

        $pool = new Pool(new Client(), $requests(), [
            'concurrency' => 10,
            'fulfilled' => function (): void {
                // do nothing
            },
            'rejected' => function (mixed $reason, int $index): void {
                $this->logger->error(
                    'Failed to deliver message',
                    ['reason' => $reason]
                );
            },
        ]);

        $pool->promise()->wait();
    }
}
