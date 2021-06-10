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

    public function matchStarting(Match $match)
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

    public function matchComplete(Match $match)
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
                $comment = "Congratulations {$match->homeTeam->buildSlackName()} :tada:";
                break;
            case 'AWAY_TEAM':
                $comment = "Congratulations {$match->awayTeam->buildSlackName()} :tada:";
                break;
            case 'DRAW':
            default:
                $comment = "It's a draw! :steamed-hams:";
        }

        $message = sprintf(
            "%s :flag-%s: %d:%d :flag-%s: %s! %s",
            $match->homeTeam->name,
            $match->homeTeam->buildSlackName(),
            $match->homeScore,
            $match->awayScore,
            $match->awayTeam->flagCode,
            $match->awayTeam->name,
            $comment
        );

        $this->sendMessage($message);
    }

    public function sendMessage(string $message)
    {
        $this->client->post('', ['json' => ['text' => $message]]);
    }
}
