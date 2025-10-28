<?php

namespace App\Listeners;

use App\Events\AccountCreated;
use App\Jobs\SendEmailNotification;
use App\Jobs\SendInAppNotification;
use App\Jobs\SendSmsNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAccountCreatedNotifications
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
    public function handle(AccountCreated $event): void
    {
        $client = $event->client;
        $password = $event->password;
        $code = $event->code;

        // Envoi email avec le mot de passe temporaire
        if ($client->email) {
            SendEmailNotification::dispatch(
                $client->email,
                "Votre compte a été créé avec succès. Voici vos informations de connexion :\n\nMot de passe temporaire : {$password}\n\nVeuillez changer votre mot de passe après votre première connexion.",
                [
                    'client' => $client,
                    'password' => $password,
                    'type' => 'account_created'
                ]
            );
        }

        // Envoi SMS avec le code de vérification
        if ($client->telephone) {
            SendSmsNotification::dispatch(
                $client->telephone,
                "Votre compte bancaire a été créé. Code de vérification : {$code}. Mot de passe temporaire : {$password}",
                [
                    'client' => $client,
                    'code' => $code,
                    'type' => 'account_created'
                ]
            );
        }

        // Notification in-app (uniquement si l'utilisateur existe)
        if ($client->user_id) {
            SendInAppNotification::dispatch(
                $client->user_id,
                "Bienvenue ! Votre compte bancaire a été créé avec succès. Un email et SMS contenant vos informations de connexion vous ont été envoyés.",
                [
                    'client' => $client,
                    'type' => 'account_created'
                ]
            );
        }
    }
}
