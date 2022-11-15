<?php

namespace Plastonick\Euros;

use DateTimeInterface;

class StaticConfigurationService implements ConfigurationServiceInterface
{
    public function __construct(private readonly Configuration $configuration)
    {
    }

    public function retrieveConfigurationsSince(DateTimeInterface $lastUpdated): array
    {
        return [$this->configuration];
    }

    public function retrieveConfiguration(string $webhookUrl): ?Configuration
    {
        return $this->configuration;
    }

    public function deleteConfiguration(string $webhookUrl): bool
    {
        return true;
    }

    public function persistConfiguration(
        string $webhookUrl,
        Service $service,
        array $teamMap,
        Emoji $winEmoji,
        Emoji $scoreEmoji,
        Emoji $drawEmoji,
        Emoji $kickoffEmoji,
        ?string $kickoffTemplate,
        ?string $scoreTemplate,
        ?string $disallowedTemplate,
        ?string $winTemplate,
        ?string $drawTemplate
    ): bool {
        return true;
    }

    public function persistTestWebhook(string $url, Service $service): bool
    {
        return true;
    }

    public function popTestWebhooks(): array
    {
        return [];
    }
}
