<?php

namespace Plastonick\Euros;

use DateTimeInterface;

class NullConfigurationService implements ConfigurationServiceInterface
{
    public function retrieveConfigurationsSince(DateTimeInterface $lastUpdated): array
    {
        return [];
    }

    public function retrieveConfiguration(string $webhookUrl): ?Configuration
    {
        return null;
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
        Emoji $kickoffEmoji
    ): bool {
        return true;
    }
}
