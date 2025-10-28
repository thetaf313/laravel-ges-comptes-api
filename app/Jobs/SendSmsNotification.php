<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Sms\TwilioSmsService;

class SendSmsNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $to,
        public string $message,
        public array $data = []
    ) {}

    public function handle(TwilioSmsService $smsService): void
    {
        if ($smsService->canSend()) {
            $smsService->send($this->to, $this->message, $this->data);
        }
    }
}
