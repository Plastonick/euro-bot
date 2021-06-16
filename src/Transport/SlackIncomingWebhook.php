<?php

namespace Plastonick\Euros\Transport;

use GuzzleHttp\Client;

class SlackIncomingWebhook implements NotificationService
{
    private Client $client;

    public function __construct(string $webhookUrl)
    {
        $this->client = new Client(
            [
                'base_uri' => $webhookUrl,
                'timeout' => 0,
                'allow_redirects' => false,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function send(string $message): void
    {
        $this->client->post('', ['json' => ['text' => $message]]);
    }
}
