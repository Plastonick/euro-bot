<?php

namespace Plastonick\Euros\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Log\LoggerInterface;

class SlackIncomingWebhook implements NotificationService
{
    public function __construct(
        private string $webhookUrl,
        private readonly Client $client,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function send(string $message): PromiseInterface
    {
        return $this->client->postAsync($this->webhookUrl, ['json' => ['text' => $message]]);
    }
}
