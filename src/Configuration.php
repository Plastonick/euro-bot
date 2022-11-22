<?php

namespace Plastonick\Euros;

use function strtoupper;
use function substr;

class Configuration
{
    public static function fromEnv(): self
    {
        $owners = [];
        foreach ($_ENV as $key => $value) {
            if (str_starts_with($key, 'TEAM_')) {
                $acronym = strtoupper(substr($key, 5, 3));
                $owners[$acronym] = $value;
            }
        }

        if (isset($_ENV['SLACK_WEB_HOOK'])) {
            $webhook = $_ENV['SLACK_WEB_HOOK'];
            $service = Service::SLACK;
        } else {
            $webhook = $_ENV['DISCORD_WEB_HOOK'];
            $service = Service::DISCORD;
        }

        return new Configuration(
            $webhook,
            $service,
            $owners,
            Emoji::createFromString($_ENV['WIN_EMOJI'] ?? ''),
            Emoji::createFromString($_ENV['SCORE_EMOJI'] ?? ''),
            Emoji::createFromString($_ENV['KICK_OFF_EMOJI'] ?? ''),
            Emoji::createFromString($_ENV['DRAW_EMOJI'] ?? ''),
            $_ENV['KICK_OFF_TEMPLATE'] ?? null,
            $_ENV['SCORE_TEMPLATE'] ?? null,
            $_ENV['DISALLOWED_TEMPLATE'] ?? null,
            $_ENV['WIN_TEMPLATE'] ?? null,
            $_ENV['DRAW_TEMPLATE'] ?? null
        );
    }

    public function __construct(
        public readonly string $webHookUrl,
        public readonly Service $service,
        private readonly array $owners,
        private readonly Emoji $win,
        private readonly Emoji $score,
        private readonly Emoji $kickOff,
        private readonly Emoji $draw,
        public readonly ?string $kickOffTemplate,
        public readonly ?string $scoreTemplate,
        public readonly ?string $disallowedTemplate,
        public readonly ?string $winTemplate,
        public readonly ?string $drawTemplate
    ) {
    }

    public function getTeamOwner(Team $team): ?string
    {
        return $this->owners[$team->tla] ?? null;
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

    public function getDelaySeconds(): int
    {
        return 120;
    }

    public function toArray(): array
    {
        return [
            'webhook' => $this->webHookUrl,
            'service' => $this->service->value,
            'owners' => $this->owners,
            'win' => $this->win->toString(),
            'score' => $this->score->toString(),
            'kickOff' => $this->kickOff->toString(),
            'draw' => $this->draw->toString(),
            'kickOffTemplate' => $this->kickOffTemplate,
            'scoreTemplate' => $this->scoreTemplate,
            'disallowedTemplate' => $this->disallowedTemplate,
            'winTemplate' => $this->winTemplate,
            'drawTemplate' => $this->drawTemplate,
        ];
    }
}
