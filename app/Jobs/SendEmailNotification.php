<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Contracts\EmailServiceInterface;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $to,
        public string $message,
        public array $data = []
    ) {}

    public function handle(EmailServiceInterface $emailService): void
    {
        if ($emailService->canSend()) {
            $emailService->send($this->to, $this->message, $this->data);
        }
    }
}