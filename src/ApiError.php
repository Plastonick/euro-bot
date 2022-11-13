<?php

namespace Plastonick\Euros;

use function json_encode;

class ApiError
{
    public function __construct(private readonly string $message)
    {
    }

    public function __toString(): string
    {
        return json_encode([
            'message' => $this->message,
        ]);
    }
}
