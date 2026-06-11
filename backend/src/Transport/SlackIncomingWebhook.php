<?php

namespace Plastonick\Euros\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;

class SlackIncomingWebhook implements NotificationService
{
    public function __construct(
        private string $webhookUrl,
        private readonly Client $client
    ) {
    }

    public function send(string $message): PromiseInterface
    {
        return $this->client->postAsync(
            $this->webhookUrl,
            ['json' => ['text' => $message]]
        );
    }
}
