<?php

namespace App\Services\Email;

use App\Contracts\EmailServiceInterface;
use App\Mail\SendPasswordMail;
// use App\Services\Email\MailgunService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService implements EmailServiceInterface
{

    public function send(string $to, string $message, $data = []): bool
    {
        try {
            // Essayer d'abord avec le mailer par défaut (SMTP)
            if (isset($data['type']) && $data['type'] === 'account_created') {
                Mail::to($to)->send(new SendPasswordMail($data['client'], $data['password'], $data));
            } else {
                Mail::raw($message, function ($mail) use ($to) {
                    $mail->to($to)->subject('Notification Ges-Comptes');
                });
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendWithTemplates(string $to, string $template, array $data = []): bool
    {
        // Implémentation pour les templates d'email
        return $this->send($to, '', $data);
    }

    public function canSend(): bool
    {
        // Vérifier si le mailer est configuré
        return config('mail.default') !== null;
    }
}
