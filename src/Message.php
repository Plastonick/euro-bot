<?php

namespace Plastonick\Euros;

use DateTimeInterface;
use Plastonick\Euros\Transport\NotificationService;

class Message
{
    public function __construct(
        public readonly NotificationService $notificationService,
        public readonly string $content,
        public readonly DateTimeInterface $sendAt
    ) {
    }
}
