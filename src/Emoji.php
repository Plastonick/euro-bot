<?php

namespace Plastonick\Euros;

use function array_rand;
use function explode;

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
        $key = array_rand($this->emoji);

        if (!isset($this->emoji[$key])) {
            return null;
        }

        return ":{$this->emoji[$key]}:";
    }
}
