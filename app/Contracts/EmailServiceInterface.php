<?php

namespace App\Contracts;

interface EmailServiceInterface extends NotificationServiceInterface
{
    public function sendWithTemplates(string $to, string $template, array $data = []): bool;
}
