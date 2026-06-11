<?php

namespace Plastonick\Euros\Transport;

use GuzzleHttp\Promise\PromiseInterface;

interface NotificationService
{
    public function send(string $message): PromiseInterface;
}
