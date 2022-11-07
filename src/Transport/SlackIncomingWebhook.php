<?php

namespace Plastonick\Euros\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SlackIncomingWebhook implements NotificationService
{
    private Client $client;

    public function __construct(string $webhookUrl)
    {
        $this->client = new Client(
            [
                'base_uri' => $webhookUrl,
                'timeout' => 3,
                'allow_redirects' => false,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function send(string $message): void
    {
        try {
            $this->client->post('', ['json' => ['text' => $message]]);
        } catch (GuzzleException $e) {
            // todo log?
        }
    }
}
