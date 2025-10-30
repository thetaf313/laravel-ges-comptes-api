<?php

namespace App\Services\Email;

use App\Contracts\EmailServiceInterface;
use App\Mail\SendPasswordMail;
use App\Services\Mail\MailgunService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService implements EmailServiceInterface
{
    protected $mailgunService;

    public function __construct(MailgunService $mailgunService)
    {
        $this->mailgunService = $mailgunService;
    }

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
            Log::warning('SMTP email failed, trying Mailgun fallback: ' . $e->getMessage());

            // Fallback vers Mailgun
            return $this->sendWithMailgun($to, $message, $data);
        }
    }

    protected function sendWithMailgun(string $to, string $message, $data = []): bool
    {
        try {
            $mailData = [
                'to' => $to,
                'subject' => 'Notification Ges-Comptes',
                'text' => $message,
            ];

            if (isset($data['type']) && $data['type'] === 'account_created') {
                $mailData['subject'] = 'Bienvenue sur Ges-Comptes';
                $mailData['html'] = view('emails.account_created', [
                    'client' => $data['client'],
                    'password' => $data['password'],
                    'data' => $data
                ])->render();
            }

            $result = $this->mailgunService->send($mailData);

            if ($result['success']) {
                Log::info('Email sent via Mailgun fallback: ' . $result['message_id']);
                return true;
            } else {
                Log::error('Mailgun fallback failed: ' . $result['error']);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Mailgun fallback exception: ' . $e->getMessage());
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
        // Vérifier si au moins un service est disponible
        return config('mail.enabled', false) || $this->mailgunService->isAvailable();
    }
}
