<?php

namespace Plastonick\Euros;

use DateTimeInterface;

interface ConfigurationServiceInterface
{
    /**
     * @param DateTimeInterface $lastUpdated
     *
     * @return Configuration[]
     */
    public function retrieveConfigurationsSince(DateTimeInterface $lastUpdated): array;

    /**
     * @param string $webhookUrl
     *
     * @return Configuration|null
     */
    public function retrieveConfiguration(string $webhookUrl): ?Configuration;

    public function deleteConfiguration(string $webhookUrl): bool;

    public function persistConfiguration(
        string $webhookUrl,
        Service $service,
        array $teamMap,
        Emoji $winEmoji,
        Emoji $scoreEmoji,
        Emoji $drawEmoji,
        Emoji $kickoffEmoji
    ): bool;

    public function persistTestWebhook(string $url, Service $service): bool;

    public function popTestWebhooks(): array;
}
