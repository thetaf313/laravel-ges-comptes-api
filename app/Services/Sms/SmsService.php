<?php

namespace App\Services;

use App\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService implements SmsServiceInterface
{
    public function send(string $to, string $message, array $data = []): bool
    {
        try {
            // Intégration avec un service SMS (Twilio, etc.)
            // Exemple avec une API fictive
            $response = Http::post(config('services.sms.endpoint'), [
                'to' => $to,
                'message' => $message,
                'api_key' => config('services.sms.api_key'),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendWithSenderId(string $to, string $message, string $senderId): bool
    {
        // Implémentation spécifique avec sender ID
        return $this->send($to, $message, ['sender_id' => $senderId]);
    }

    public function canSend(): bool
    {
        return config('services.sms.enabled', false);
    }
}