<?php

namespace Plastonick\Euros\Transport;

interface NotificationService
{
    /**
     * @param string $message
     */
    public function send(string $message): void;
}
