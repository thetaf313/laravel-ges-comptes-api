<?php


namespace App\Contracts;

interface InAppNotificationInterface extends NotificationServiceInterface
{
    public function sendToUser(string $userId, string $message, array $data = []): bool;
    public function markAsRead(int $notificationId): bool;
}
