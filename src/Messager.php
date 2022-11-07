<?php

namespace Plastonick\Euros;

use Plastonick\Euros\Transport\NotificationService;
use function strtr;

class Messager
{
    private Emoji $emoji;
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->emoji = new Emoji();
        $this->notificationService = $notificationService;
    }

    public function matchStarting(Game $match): void
    {
        if ($match->homeTeam === null) {
            return;
        }

        if ($match->awayTeam === null) {
            return;
        }

        $template = '{homeName} {homeOwner} {homeFlag} vs. {awayFlag} {awayName} {awayOwner} has kicked off! {kickOffEmoji}';
        $replacements = [
            '{homeName}' => $match->homeTeam->name,
            '{homeOwner}' => $match->homeTeam->buildSlackName(),
            '{homeFlag}' => $match->homeTeam->getFlagEmoji(),
            '{awayFlag}' => $match->awayTeam->getFlagEmoji(),
            '{awayName}' => $match->awayTeam->name,
            '{awayOwner}' => $match->awayTeam->buildSlackName(),
            '{kickOffEmoji}' => $this->emoji->getKickOffEmoji()
        ];

        $this->notificationService->send(strtr($template, $replacements));
    }

    public function matchComplete(Game $match): void
    {
        if ($match->winner === null) {
            return;
        }

        if ($match->homeTeam === null) {
            return;
        }

        if ($match->awayTeam === null) {
            return;
        }

        $comment = match ($match->winner) {
            'HOME_TEAM' => "Congratulations {$match->homeTeam->name} {$this->emoji->getWinEmoji()}",
            'AWAY_TEAM' => "Congratulations {$match->awayTeam->name} {$this->emoji->getWinEmoji()}",
            default => "It's a draw! {$this->emoji->getDrawEmoji()}",
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
            '{homeFlag}' => $match->homeTeam?->getFlagEmoji(),
            '{homeScore}' => (int) $match->homeScore,
            '{awayFlag}' => $match->awayTeam?->getFlagEmoji(),
            '{awayScore}' => (int) $match->awayScore,
            '{scoreEmoji}' => $this->emoji->getScoreEmoji()
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
            '{scoreEmoji}' => $this->emoji->getScoreEmoji()
        ];

        $this->notificationService->send(strtr($template, $replacements));
    }
}
