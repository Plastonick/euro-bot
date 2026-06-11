<?php

namespace Plastonick\Euros;

use DateTime;
use DateTimeInterface;
use GuzzleHttp\Client;
use Plastonick\Euros\Transport\DiscordIncomingWebhook;
use Plastonick\Euros\Transport\NotificationService;
use Plastonick\Euros\Transport\SlackIncomingWebhook;
use function preg_replace;
use function str_starts_with;
use function strtr;
use function time;

class Messenger implements Messenging
{
    public function __construct(
        private readonly Client $client,
        public readonly Configuration $config
    ) {
    }

    public function matchStarting(Game $match): Message
    {
        $template = $this->config->kickOffTemplate ?? '{homeName} {homeOwner} {homeFlag} vs. {awayFlag} {awayName} {awayOwner} has kicked off! {kickOffEmoji}';
        $replacements = [
            '{homeName}' => $match->homeTeam->name,
            '{homeOwner}' => $this->buildName($this->config->getTeamOwner($match->homeTeam)),
            '{homeFlag}' => $match->homeTeam->getFlagEmoji(),
            '{awayFlag}' => $match->awayTeam->getFlagEmoji(),
            '{awayName}' => $match->awayTeam->name,
            '{awayOwner}' => $this->buildName($this->config->getTeamOwner($match->awayTeam)),
            '{kickOffEmoji}' => $this->config->getKickOffEmoji()
        ];

        return new Message(
            $this->getNotificationService(),
            $this->buildMessage($template, $replacements),
            $this->buildDelayUntil()
        );
    }

    public function matchComplete(Game $match): Message
    {
        return match ($match->winner) {
            'HOME_TEAM' => $this->matchWon($match->homeTeam, $match->awayTeam, $match),
            'AWAY_TEAM' => $this->matchWon($match->awayTeam, $match->homeTeam, $match),
            default => $this->matchDrawn($match),
        };
    }

    public function goalScored(Team $scoringTeam, Game $match): Message
    {
        $template = $this->config->scoreTemplate ?? '{scoringTeam} score! {scoreEmoji} — {homeFlag} {homeScore} : {awayScore} {awayFlag}';
        $replacements = [
            '{scoringTeam}' => $scoringTeam->name,
            '{homeFlag}' => $match->homeTeam->getFlagEmoji(),
            '{homeScore}' => (int) $match->homeScore,
            '{awayFlag}' => $match->awayTeam->getFlagEmoji(),
            '{awayScore}' => (int) $match->awayScore,
            '{scoreEmoji}' => $this->config->getScoreEmoji()
        ];

        return new Message(
            $this->getNotificationService(),
            $this->buildMessage($template, $replacements),
            $this->buildDelayUntil()
        );
    }

    public function goalDisallowed(Game $match): Message
    {
        $template = $this->config->disallowedTemplate ?? 'NO GOAL! {homeFlag} {homeScore} : {awayScore} {awayFlag}';
        $replacements = [
            '{homeFlag}' => $match->homeTeam->getFlagEmoji(),
            '{homeScore}' => (int) $match->homeScore,
            '{awayFlag}' => $match->awayTeam->getFlagEmoji(),
            '{awayScore}' => (int) $match->awayScore,
            '{scoreEmoji}' => $this->config->getScoreEmoji()
        ];

        return new Message(
            $this->getNotificationService(),
            $this->buildMessage($template, $replacements),
            $this->buildDelayUntil()
        );
    }

    private function matchWon(Team $winner, Team $loser, Game $game): Message
    {
        $template = $this->config->winTemplate ?? '{winnerName} {winnerFlag} {winnerScore} : {loserScore} {loserFlag} {loserName}! Congratulations {winnerName} {winEmoji}';
        $replacements = [
            '{winnerName}' => $winner->name,
            '{winnerFlag}' => $winner->getFlagEmoji(),
            '{winnerScore}' => max($game->homeScore, $game->awayScore),
            '{loserScore}' => min($game->homeScore, $game->awayScore),
            '{loserFlag}' => $loser->getFlagEmoji(),
            '{loserName}' => $loser->name,
            '{winEmoji}' => $this->config->getWinEmoji(),
        ];

        return new Message(
            $this->getNotificationService(),
            $this->buildMessage($template, $replacements),
            $this->buildDelayUntil()
        );
    }

    private function matchDrawn(Game $game): Message
    {
        $template = $this->config->drawTemplate ?? '{homeName} {homeFlag} {homeScore} : {awayScore} {awayFlag} {awayName}! It\'s a draw! {drawEmoji}';
        $replacements = [
            '{homeName}' => $game->homeTeam->name,
            '{homeFlag}' => $game->homeTeam->getFlagEmoji(),
            '{homeScore}' => $game->homeScore,
            '{awayScore}' => $game->awayScore,
            '{awayFlag}' => $game->awayTeam->getFlagEmoji(),
            '{awayName}' => $game->awayTeam->name,
            '{drawEmoji}' => $this->config->getDrawEmoji(),
        ];

        return new Message(
            $this->getNotificationService(),
            $this->buildMessage($template, $replacements),
            $this->buildDelayUntil()
        );
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

    private function buildDelayUntil(): DateTimeInterface
    {
        $timestamp = time() + $this->config->delaySeconds;

        return DateTime::createFromFormat('U', (string) $timestamp);
    }

    private function buildName(?string $owner): string
    {
        if ($owner === null) {
            return '';
        }

        if (str_starts_with($owner, '@')) {
            return "<{$owner}>";
        }

        return "({$owner})";
    }
}
