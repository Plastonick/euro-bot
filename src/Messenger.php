<?php

namespace Plastonick\Euros;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Plastonick\Euros\Transport\DiscordIncomingWebhook;
use Plastonick\Euros\Transport\NotificationService;
use Plastonick\Euros\Transport\SlackIncomingWebhook;
use function preg_replace;
use function str_starts_with;
use function strtr;

class Messenger implements Messenging
{
    public function __construct(
        private readonly Client $client,
        public readonly Configuration $config
    ) {
    }

    public function matchStarting(Game $match): PromiseInterface
    {
        $template = '{homeName} {homeOwner} {homeFlag} vs. {awayFlag} {awayName} {awayOwner} has kicked off! {kickOffEmoji}';
        $replacements = [
            '{homeName}' => $match->homeTeam->name,
            '{homeOwner}' => $this->buildName($this->config->getTeamOwner($match->homeTeam)),
            '{homeFlag}' => $match->homeTeam->getFlagEmoji($this->config->service),
            '{awayFlag}' => $match->awayTeam->getFlagEmoji($this->config->service),
            '{awayName}' => $match->awayTeam->name,
            '{awayOwner}' => $this->buildName($this->config->getTeamOwner($match->awayTeam)),
            '{kickOffEmoji}' => $this->config->getKickOffEmoji()
        ];

        return $this->getNotificationService()->send($this->buildMessage($template, $replacements));
    }

    public function matchComplete(Game $match): PromiseInterface
    {
        $comment = match ($match->winner) {
            'HOME_TEAM' => "Congratulations {$match->homeTeam->name} {$this->config->getWinEmoji()}",
            'AWAY_TEAM' => "Congratulations {$match->awayTeam->name} {$this->config->getWinEmoji()}",
            default => "It's a draw! {$this->config->getDrawEmoji()}",
        };

        $template = '{homeName} {homeFlag} {homeScore} : {awayScore} {awayFlag} {awayName}! {comment}';
        $replacements = [
            '{homeName}' => $match->homeTeam->name,
            '{homeFlag}' => $match->homeTeam->getFlagEmoji($this->config->service),
            '{homeScore}' => (int) $match->homeScore,
            '{awayScore}' => (int) $match->awayScore,
            '{awayFlag}' => $match->awayTeam->getFlagEmoji($this->config->service),
            '{awayName}' => $match->awayTeam->name,
            '{comment}' => $comment
        ];

        return $this->getNotificationService()->send($this->buildMessage($template, $replacements));
    }

    public function goalScored(Team $scoringTeam, Game $match): PromiseInterface
    {
        $template = '{scoringTeam} score! {scoreEmoji} — {homeFlag} {homeScore} : {awayScore} {awayFlag}';
        $replacements = [
            '{scoringTeam}' => $scoringTeam->name,
            '{homeFlag}' => $match->homeTeam->getFlagEmoji($this->config->service),
            '{homeScore}' => (int) $match->homeScore,
            '{awayFlag}' => $match->awayTeam->getFlagEmoji($this->config->service),
            '{awayScore}' => (int) $match->awayScore,
            '{scoreEmoji}' => $this->config->getScoreEmoji()
        ];

        return $this->getNotificationService()->send($this->buildMessage($template, $replacements));
    }

    public function goalDisallowed(Game $match): PromiseInterface
    {
        $template = 'NO GOAL! {homeFlag} {homeScore} : {awayScore} {awayFlag}';
        $replacements = [
            '{homeFlag}' => $match->homeTeam->getFlagEmoji($this->config->service),
            '{homeScore}' => (int) $match->homeScore,
            '{awayFlag}' => $match->awayTeam->getFlagEmoji($this->config->service),
            '{awayScore}' => (int) $match->awayScore,
            '{scoreEmoji}' => $this->config->getScoreEmoji()
        ];

        return $this->getNotificationService()->send($this->buildMessage($template, $replacements));
    }

    private function buildMessage(string $template, array $replacements): string
    {
        return preg_replace('/\s{2,}/', ' ', strtr($template, $replacements));
    }

    private function getNotificationService(): NotificationService
    {
        return match ($this->config->service) {
            Service::SLACK => new SlackIncomingWebhook($this->config->webHookUrl, $this->client),
            Service::DISCORD => new DiscordIncomingWebhook($this->config->webHookUrl, $this->client)
        };
    }

    private function buildName(?string $owner): string
    {
        if ($owner === null) {
            return '';
        }

        if (str_starts_with($owner, '@')) {
            return "<{$owner}>";
        }

        return $owner;
    }
}
