<?php

namespace App\Services\Mail;

use Mailgun\Mailgun;
use Exception;

class MailgunService
{
    protected $mailgun;
    protected $domain;

    public function __construct()
    {
        $this->mailgun = Mailgun::create(config('services.mailgun.secret'));
        $this->domain = config('services.mailgun.domain');
    }

    /**
     * Envoyer un email via Mailgun
     */
    public function send(array $data)
    {
        try {
            $result = $this->mailgun->messages()->send($this->domain, [
                'from' => $data['from'] ?? config('mail.from.address'),
                'to' => $data['to'],
                'subject' => $data['subject'],
                'text' => $data['text'] ?? '',
                'html' => $data['html'] ?? null,
            ]);

            return [
                'success' => true,
                'message_id' => $result->getId(),
                'message' => $result->getMessage(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier si le service est disponible
     */
    public function isAvailable()
    {
        try {
            // Test simple en vérifiant le domaine
            $this->mailgun->domains()->show($this->domain);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
