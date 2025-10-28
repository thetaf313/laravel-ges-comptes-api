<?php

namespace App\Services\Notification;

use App\Contracts\InAppNotificationInterface;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class InAppNotificationService implements InAppNotificationInterface
{
    public function send(string $to, string $message, array $data = []): bool
    {
        return $this->sendToUser((int) $to, $message, $data);
    }

    public function sendToUser(int $userId, string $message, array $data = []): bool
    {
        try {
            $user = User::find($userId);

            if ($user) {
                $user->notifications()->create([
                    'message' => $message,
                    'data' => $data,
                    'type' => $data['type'] ?? 'account_created',
                    'read_at' => null,
                ]);

                // Déclencher un event pour les notifications en temps réel
                event(new \App\Events\NotificationSent($userId, $message));

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('In-app notification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function markAsRead(int $notificationId): bool
    {
        try {
            Notification::where('id', $notificationId)->update(['read_at' => now()]);
            return true;
        } catch (\Exception $e) {
            Log::error('Mark notification as read failed: ' . $e->getMessage());
            return false;
        }
    }

    public function canSend(): bool
    {
        return true; // Toujours disponible
    }
}
