<?php

namespace Plastonick\Euros;

use function strtolower;
use function substr;

class Configuration
{
    public static function fromEnv(): self
    {
        $owners = [];
        foreach ($_ENV as $key => $value) {
            if (str_starts_with($key, 'TEAM_')) {
                $acronym = strtolower(substr($key, 5, 3));
                $owners[$acronym] = $value;
            }
        }

        return new Configuration(
            $_ENV['SLACK_WEB_HOOK'],
            Service::SLACK,
            $owners,
            Emoji::createFromString($_ENV['WIN_EMOJI'] ?? ''),
            Emoji::createFromString($_ENV['SCORE_EMOJI'] ?? ''),
            Emoji::createFromString($_ENV['KICK_OFF_EMOJI'] ?? ''),
            Emoji::createFromString($_ENV['DRAW_EMOJI'] ?? '')
        );
    }

    public function __construct(
        public readonly string $webHookUrl,
        public readonly Service $service,
        private readonly array $owners,
        private readonly Emoji $win,
        private readonly Emoji $score,
        private readonly Emoji $kickOff,
        private readonly Emoji $draw
    ) {
    }

    public function getTeamOwner(Team $team): ?string
    {
        return $this->owners[$team->acronym] ?? null;
    }

    public function getWinEmoji(): ?string
    {
        return $this->win->retrieveRandomEmoji();
    }

    public function getScoreEmoji(): ?string
    {
        return $this->score->retrieveRandomEmoji();
    }

    public function getKickOffEmoji(): ?string
    {
        return $this->kickOff->retrieveRandomEmoji();
    }

    public function getDrawEmoji(): ?string
    {
        return $this->draw->retrieveRandomEmoji();
    }
}
