<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendClientNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(\App\Events\SendClientNotification $event): void
    {
        // Envoyer l'email d'authentification avec le mot de passe
        try {
            Mail::raw("Votre compte a été créé. Mot de passe temporaire : {$event->password}", function ($message) use ($event) {
                $message->to($event->client->user->email)
                    ->subject('Authentification - Compte créé');
            });
        } catch (\Exception $e) {
            Log::error('Erreur envoi email: ' . $e->getMessage());
        }

        // Envoyer le code par SMS (simulation avec log)
        Log::info('SMS envoyé au ' . $event->client->telephone . ' avec le code : ' . $event->code);
        // Ici, intégrer un service SMS comme Twilio ou AfricasTalking
    }
}
