<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Client;
use App\Models\Admin;

trait HasUserScopes
{
    /**
     * Scope pour filtrer automatiquement selon les permissions utilisateur
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->authenticatable_type === Admin::class) {
            // Admin voit tout
            return $query;
        }

        if ($user->authenticatable_type === Client::class) {
            // Client ne voit que ses propres comptes
            return $query->where('client_id', $user->authenticatable_id);
        }

        // Par défaut, aucun résultat
        return $query->whereNull('id');
    }

    /**
     * Vérifier si l'utilisateur peut voir ce modèle
     */
    public function canBeViewedBy(User $user): bool
    {
        return $user->authenticatable_type === Admin::class ||
            ($user->authenticatable_type === Client::class && $this->client_id === $user->authenticatable_id);
    }

    /**
     * Vérifier si l'utilisateur peut modifier ce modèle
     */
    public function canBeUpdatedBy(User $user): bool
    {
        return $user->authenticatable_type === Admin::class ||
            ($user->authenticatable_type === Client::class && $this->client_id === $user->authenticatable_id);
    }

    /**
     * Vérifier si l'utilisateur peut créer ce type de modèle
     */
    public static function canBeCreatedBy(User $user): bool
    {
        return $user->authenticatable_type === Admin::class;
    }

    /**
     * Vérifier si l'utilisateur peut supprimer ce modèle
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $user->authenticatable_type === Admin::class;
    }

    /**
     * Vérifier si l'utilisateur peut bloquer ce modèle
     */
    public function canBeBlockedBy(User $user): bool
    {
        return $user->authenticatable_type === Admin::class;
    }
}
