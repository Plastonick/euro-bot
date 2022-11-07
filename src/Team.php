<?php

namespace Plastonick\Euros;

class Team
{
    public int $id;
    public string $name;
    public string $flagCode;
    public ?string $owner;

    public function __construct(int $id, string $name, string $flagCode, ?string $owner)
    {
        $this->id = $id;
        $this->name = $name;
        $this->flagCode = $flagCode;
        $this->owner = $owner;
    }

    public function buildSlackName(): string
    {
        if ($this->owner) {
            return "<@{$this->owner}>";
        }

        if ($this->owner === null) {
            return "Unknown";
        }

        return $this->owner;
    }

    public function getFlagEmoji(): string
    {
        return ":flag-{$this->flagCode}:";
    }
}
