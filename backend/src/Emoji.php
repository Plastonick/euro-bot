<?php

namespace Plastonick\Euros;

use function array_rand;
use function explode;
use function implode;
use function preg_match;

class Emoji
{
    public static function createFromString(string $list): self
    {
        if ($list === '') {
            return new self([]);
        } else {
            return new self(explode(',', $list));
        }
    }

    public function __construct(private readonly array $emoji)
    {
    }

    public function retrieveRandomEmoji(): ?string
    {
        if (!$this->emoji) {
            return null;
        }

        $key = array_rand($this->emoji);

        $emojiString = $this->emoji[$key] ?? null;
        if ($emojiString === null) {
            return null;
        }

        $emojiString = trim($emojiString);
        if (preg_match('/^[a-zA-Z\d\-_]*$/', $emojiString)) {
            return ":{$emojiString}:";
        } else {
            return $emojiString;
        }

    }

    public function toString(): string
    {
        return implode(',', $this->emoji);
    }
}
