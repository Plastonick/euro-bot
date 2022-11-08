<?php

namespace Plastonick\Euros;

use Plastonick\Euros\Transport\NotificationService;
use function strtr;

class Messenger implements Messenging
{
    public function __construct(
        private readonly NotificationService $notificationService,
        public readonly Configuration $config
    ) {
    }

    public function matchStarting(Game $match): void
    {
        $template = '{homeName} {homeOwner} {homeFlag} vs. {awayFlag} {awayName} {awayOwner} has kicked off! {kickOffEmoji}';
        $replacements = [
            '{homeName}' => $match->homeTeam->name,
            '{homeOwner}' => $this->buildName($this->config->getTeamOwner($match->homeTeam)),
            '{homeFlag}' => $match->homeTeam->getFlagEmoji(),
            '{awayFlag}' => $match->awayTeam->getFlagEmoji(),
            '{awayName}' => $match->awayTeam->name,
            '{awayOwner}' => $this->buildName($this->config->getTeamOwner($match->awayTeam)),
            '{kickOffEmoji}' => $this->config->getKickOffEmoji()
        ];

        $this->notificationService->send(strtr($template, $replacements));
    }

    public function matchComplete(Game $match): void
    {
        if ($match->winner === null) {
            return;
        }

        $comment = match ($match->winner) {
            'HOME_TEAM' => "Congratulations {$match->homeTeam->name} {$this->config->getWinEmoji()}",
            'AWAY_TEAM' => "Congratulations {$match->awayTeam->name} {$this->config->getWinEmoji()}",
            default => "It's a draw! {$this->config->getDrawEmoji()}",
        };

        $template = '{homeName} {homeFlag} {homeScore} : {awayScore} {awayFlag} {awayName}! {comment}';
        $replacements = [
            '{homeName}' => $match->homeTeam->name,
            '{homeFlag}' => $match->homeTeam->getFlagEmoji(),
            '{homeScore}' => (int) $match->homeScore,
            '{awayScore}' => (int) $match->awayScore,
            '{awayFlag}' => $match->awayTeam->getFlagEmoji(),
            '{awayName}' => $match->awayTeam->name,
            '{comment}' => $comment
        ];

        $this->notificationService->send(strtr($template, $replacements));
    }

    public function goalScored(Team $scoringTeam, Game $match): void
    {
        $template = '{scoringTeam} score! {scoreEmoji} — {homeFlag} {homeScore} : {awayScore} {awayFlag}';
        $replacements = [
            '{scoringTeam}' => $scoringTeam->name,
            '{homeFlag}' => $match->homeTeam->getFlagEmoji(),
            '{homeScore}' => (int) $match->homeScore,
            '{awayFlag}' => $match->awayTeam->getFlagEmoji(),
            '{awayScore}' => (int) $match->awayScore,
            '{scoreEmoji}' => $this->config->getScoreEmoji()
        ];

        $this->notificationService->send(strtr($template, $replacements));
    }

    public function goalDisallowed(Game $match): void
    {
        $template = 'NO GOAL! {homeFlag} {homeScore} : {awayScore} {awayFlag}';
        $replacements = [
            '{homeFlag}' => $match->homeTeam->getFlagEmoji(),
            '{homeScore}' => (int) $match->homeScore,
            '{awayFlag}' => $match->awayTeam->getFlagEmoji(),
            '{awayScore}' => (int) $match->awayScore,
            '{scoreEmoji}' => $this->config->getScoreEmoji()
        ];

        $this->notificationService->send(strtr($template, $replacements));
    }

    private function buildName(?string $owner): string
    {
        if ($owner === null) {
            return '';
        }

        if (ctype_lower($owner)) {
            return "<@{$owner}>";
        }

        return $owner;
    }
}
