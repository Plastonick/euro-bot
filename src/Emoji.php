<?php

namespace Plastonick\Euros;

use function array_rand;
use function explode;

class Emoji
{
    public function getWinEmoji(): ?string
    {
        return $this->retrieveRandomEmoji('WIN_EMOJI');
    }

    public function getScoreEmoji(): ?string
    {
        return $this->retrieveRandomEmoji('SCORE_EMOJI');
    }

    public function getKickOffEmoji(): ?string
    {
        return $this->retrieveRandomEmoji('KICK_OFF_EMOJI');
    }

    public function getDrawEmoji(): ?string
    {
        return $this->retrieveRandomEmoji('DRAW_EMOJI');
    }

    private function retrieveRandomEmoji(string $key): ?string
    {
        if (!isset($_ENV[$key]) || $_ENV[$key] === '') {
            return null;
        }

        $emoji = explode(',', $_ENV[$key] ?? '');

        $key = array_rand($emoji, 1);

        return ":{$emoji[$key]}:" ?? null;
    }
}
