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
        $clientData = $event->clientData;

        // Envoi email avec le mot de passe temporaire et le code
        if ($clientData->email) {
            // Passer un payload compatible avec EmailService / SendPasswordMail
            SendEmailNotification::dispatch(
                $clientData->email,
                "Votre compte bancaire a été créé avec succès !\n\n" .
                    "Numéro de compte : {$clientData->numeroCompte}\n" .
                    "Titulaire : {$clientData->getNomComplet()}\n" .
                    "Mot de passe temporaire : {$clientData->password}\n" .
                    "Code de vérification : {$clientData->code}\n\n" .
                    "Veuillez vous connecter et changer votre mot de passe.",
                [
                    'client' => $clientData, // DTO exposes the same properties used by the mailable
                    'password' => $clientData->password,
                    'code' => $clientData->code,
                    'type' => 'account_created'
                ]
            );
        }

        // Envoi SMS avec le code de vérification
        if ($clientData->telephone) {
            SendSmsNotification::dispatch(
                $clientData->telephone,
                "Votre compte bancaire {$clientData->numeroCompte} a été créé. " .
                    "Code de vérification : {$clientData->code}. " .
                    "Mot de passe temporaire : {$clientData->password}",
                [
                    'clientData' => $clientData,
                    'type' => 'account_created'
                ]
            );
        }

        // Notification in-app (toujours envoyer car l'utilisateur est créé)
        SendInAppNotification::dispatch(
            $clientData->userId,
            "Bienvenue {$clientData->getNomComplet()} ! Votre compte bancaire {$clientData->numeroCompte} a été créé avec succès. Un email et SMS contenant vos informations de connexion vous ont été envoyés.",
            [
                'clientData' => $clientData,
                'type' => 'account_created'
            ]
        );
    }
}
