<?php

namespace App\Services\Sms;

use App\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioSmsService implements SmsServiceInterface
{
    protected $twilioClient;
    protected $fromNumber;

    public function __construct()
    {
        $this->twilioClient = new Client(config('services.twilio.sid'), config('services.twilio.auth_token'));
        $this->fromNumber = config('services.twilio.number');
    }

    public function sendSms(string $to, string $message): bool
    {
        try {
            $this->twilioClient->messages->create($to, [
                'from' => $this->fromNumber,
                'body' => $message,
            ]);
            return true;
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            Log::error('Twilio SMS Error: ' . $e->getMessage());
            return false;
        }
    }

    public function send(string $to, string $message, array $data = []): bool
    {
        return $this->sendSms($to, $message);
    }

    public function sendWithSenderId(string $to, string $message, string $senderId): bool
    {
        // Implémentation spécifique avec sender ID
        return $this->sendSms($to, $message); // Note: Twilio does not support custom sender IDs directly; adjust if needed
    }

    public function canSend(): bool
    {
        return config('services.twilio.enabled', false);
    }
}
