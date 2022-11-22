<?php

namespace Plastonick\Euros;

use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use function count;

class MessengerCollection
{
    /**
     * @var Messenger[]
     */
    private array $messengers = [];

    public function __construct(private readonly LoggerInterface $logger)
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
        $promises = [];
        foreach ($this->messengers as $messager) {
            $promises[] = $messager->matchStarting($match);
        }

        $this->dispatchPromises($promises);
    }

    public function matchComplete(Game $match): void
    {
        $promises = [];
        foreach ($this->messengers as $messager) {
            $promises[] = $messager->matchComplete($match);
        }

        $this->dispatchPromises($promises);
    }

    public function goalScored(Team $scoringTeam, Game $match): void
    {
        $promises = [];
        foreach ($this->messengers as $messager) {
            $promises[] = $messager->goalScored($scoringTeam, $match);
        }

        $this->dispatchPromises($promises);
    }

    public function goalDisallowed(Game $match): void
    {
        $promises = [];
        foreach ($this->messengers as $messager) {
            $promises[] = $messager->goalDisallowed($match);
        }

        $this->dispatchPromises($promises);
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
