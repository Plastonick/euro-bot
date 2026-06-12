<?php

namespace Plastonick\Euros;

use Psr\Log\LoggerInterface;

use function array_shift;

final class MessageQueue
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * @var Message[]
     */
    private array $cache = [];

    public function add(Message $message): void
    {
        $this->cache[] = $message;
    }

    /**
     * @return Message[]
     */
    public function retrieveReady(): array
    {
        $ready = [];
        $waiting = [];
        $time = time();

        while ($message = $this->pop()) {
            if ($message->sendAt->getTimestamp() <= $time) {
                $ready[] = $message;
            } else {
                $waiting[] = $message;
            }
        }

        foreach ($waiting as $message) {
            $this->add($message);
        }

        return $ready;
    }

    private function pop(): ?Message
    {
        return array_shift($this->cache);
    }
}
