<?php

namespace Plastonick\Euros;

use GuzzleHttp\Client;
use function sprintf;

class Slacker
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(
            [
                'base_uri' => $_ENV['SLACK_WEB_HOOK'],
                'timeout' => 0,
                'allow_redirects' => false,
            ]
        );
    }

    public function matchStarting(Match $match): void
    {
        if ($match->homeTeam === null) {
            return;
        }

        if ($match->awayTeam === null) {
            return;
        }

        $message = sprintf(
            "%s (%s) :flag-%s: vs. :flag-%s: %s (%s) has kicked off! :tada:",
            $match->homeTeam->name,
            $match->homeTeam->buildSlackName(),
            $match->homeTeam->flagCode,
            $match->awayTeam->flagCode,
            $match->awayTeam->name,
            $match->awayTeam->buildSlackName(),
        );

        $this->sendMessage($message);
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
                $comment = "Congratulations {$match->homeTeam->name} :good-job:";
                break;
            case 'AWAY_TEAM':
                $comment = "Congratulations {$match->awayTeam->name} :good-job:";
                break;
            case 'DRAW':
            default:
                $comment = "It's a draw! :steamed-hams:";
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
        $template = '{scoringTeam} score! :excellent: — {homeFlag} {homeScore} : {awayScore} {awayFlag}';
        $replacements = [
            '{scoringTeam}' => $scoringTeam->name,
            '{homeFlag}' => $match->homeTeam->getFlagEmoji(),
            '{homeScore}' => (int) $match->homeScore,
            '{awayFlag}' => $match->awayTeam->getFlagEmoji(),
            '{awayScore}' => (int) $match->awayScore
        ];

        $this->sendMessage(strtr($template, $replacements));
    }

    private function sendMessage(string $message): void
    {
        $this->client->post('', ['json' => ['text' => $message]]);
    }
}
