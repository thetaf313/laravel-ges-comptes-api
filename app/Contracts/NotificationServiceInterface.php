<?php

namespace App\Contracts;

interface NotificationServiceInterface
{
    public function send(string $to, string $message, array $data = []): bool;

    public function canSend(): bool;
}