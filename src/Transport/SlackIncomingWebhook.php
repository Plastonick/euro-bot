<?php

namespace Plastonick\Euros\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
    public function send(string $message): void
    {
        try {
            $this->client->post($this->webhookUrl, ['json' => ['text' => $message]]);
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to send message', ['url' => $this->webhookUrl, 'error' => $e->getMessage()]);
        }
    }
}
