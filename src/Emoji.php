<?php

namespace Plastonick\Euros;

use function array_rand;
use function explode;
use function is_string;

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
        $emojiList = $_ENV[$key] ?? null;
        if (!is_string($emojiList) || $emojiList === '') {
            return null;
        }

        $emoji = explode(',', $emojiList);

        $key = array_rand($emoji, 1);

        return ":{$emoji[$key]}:" ?? null;
    }
}
