<?php

namespace Plastonick\Euros\Transport;

use Throwable;

interface NotificationService
{
    /**
     * @param string $message
     *
     * @throws Throwable
     */
    public function send(string $message): void;
}
