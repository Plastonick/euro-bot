<?php

namespace Plastonick\Euros;

use GuzzleHttp\Client;
use function strtr;

class Slacker
{
    private Client $client;
    private Emoji $emoji;

    public function __construct()
    {
        $this->client = new Client(
            [
                'base_uri' => $_ENV['SLACK_WEB_HOOK'],
                'timeout' => 0,
                'allow_redirects' => false,
            ]
        );
        $this->emoji = new Emoji();
    }

    public function matchStarting(Match $match): void
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

        $this->sendMessage(strtr($template, $replacements));
    }

    public function matchComplete(Match $match): void
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

        switch ($match->winner) {
            case 'HOME_TEAM':
                $comment = "Congratulations {$match->homeTeam->name} {$this->emoji->getWinEmoji()}";
                break;
            case 'AWAY_TEAM':
                $comment = "Congratulations {$match->awayTeam->name} {$this->emoji->getWinEmoji()}";
                break;
            case 'DRAW':
            default:
                $comment = "It's a draw! {$this->emoji->getDrawEmoji()}";
        }

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

        $this->sendMessage(strtr($template, $replacements));
    }

    public function goalScored(Team $scoringTeam, Match $match): void
    {
        $template = '{scoringTeam} score! {scoreEmoji} — {homeFlag} {homeScore} : {awayScore} {awayFlag}';
        $replacements = [
            '{scoringTeam}' => $scoringTeam->name,
            '{homeFlag}' => $match->homeTeam->getFlagEmoji(),
            '{homeScore}' => (int) $match->homeScore,
            '{awayFlag}' => $match->awayTeam->getFlagEmoji(),
            '{awayScore}' => (int) $match->awayScore,
            '{scoreEmoji}' => $this->emoji->getScoreEmoji()
        ];

        $this->sendMessage(strtr($template, $replacements));
    }

    private function sendMessage(string $message): void
    {
        $this->client->post('', ['json' => ['text' => $message]]);
    }
}
