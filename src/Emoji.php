<?php

namespace Plastonick\Euros;

use function array_rand;
use function explode;
use function implode;

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

        if (!isset($this->emoji[$key])) {
            return null;
        }

        return ":{$this->emoji[$key]}:";
    }

    public function toString(): string
    {
        return implode(',', $this->emoji);
    }
}
