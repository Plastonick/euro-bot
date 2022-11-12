<?php

namespace Plastonick\Euros;

use function strlen;

class Team
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly CountryCode $countryCode,
        public readonly string $flagCode
    ) {
    }

    public function getFlagEmoji(Service $service): string
    {
        $prefix = match ($service) {
            Service::DISCORD => 'flag_',
            Service::SLACK => 'flag-'
        };

        // Handles the UK countries competing as distinct entities.
        // Discord flag emoji for England is merely `england` whilst Slack uses `flag-england`
        if ($service->is(Service::DISCORD) && strlen($this->flagCode) > 2) {
            $prefix = '';
        }

        return ":{$prefix}{$this->flagCode}:";
    }
}
