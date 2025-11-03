<?php

namespace App\Policies;

use App\Models\Compte;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ComptePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Utilise la méthode du trait pour la logique
        return true; // Le filtrage se fait via le scope forUser()
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Compte $compte): bool
    {
        return $compte->canBeViewedBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return Compte::canBeCreatedBy($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Compte $compte): bool
    {
        return $compte->canBeUpdatedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Compte $compte): bool
    {
        return $compte->canBeDeletedBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Compte $compte): bool
    {
        return $compte->canBeDeletedBy($user); // Même logique que delete
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Compte $compte): bool
    {
        return $compte->canBeDeletedBy($user); // Même logique que delete
    }

    /**
     * Determine whether the user can block/unblock the model.
     */
    public function block(User $user, Compte $compte): bool
    {
        return $compte->canBeBlockedBy($user);
    }
}
