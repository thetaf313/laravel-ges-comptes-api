<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Passport::loadKeysFrom(__DIR__ . '/../../storage');
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // 🎯 Définition des scopes OAuth2
        Passport::tokensCan([
            'read-comptes' => 'Lire les comptes',
            'create-comptes' => 'Créer des comptes',
            'update-comptes' => 'Modifier les comptes',
            'delete-comptes' => 'Supprimer les comptes',
            'block-comptes' => 'Bloquer/débloquer les comptes',
            'manage-clients' => 'Gérer les clients',
            'admin-access' => 'Accès administrateur complet',
        ]);

        // 🎯 Scopes par défaut (optionnel)
        Passport::setDefaultScope([
            'read-comptes'
        ]);
    }
}
