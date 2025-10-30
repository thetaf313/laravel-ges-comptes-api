<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Contracts\InAppNotificationInterface;

class SendInAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $userId,
        public string $message,
        public array $data = []
    ) {}

    public function handle(InAppNotificationInterface $notificationService): void
    {
        if ($notificationService->canSend()) {
            $notificationService->sendToUser($this->userId, $this->message, $this->data);
        }
    }
}
