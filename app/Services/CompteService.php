<?php

namespace App\Services;

use App\Models\Compte;
use App\Models\Transaction;
use App\Exceptions\ApiException;
use App\Exceptions\CompteNotFoundException;
use App\Exceptions\CompteArchivedException;
use App\Exceptions\NumeroCompteAlreadyExistsException;
use App\Traits\UuidValidation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;


class CompteService
{
    use UuidValidation;

    public function generateAccountNumber(): string
    {
        do {
            $number = 'CPT-' . strtoupper(Str::random(8));
        } while (Compte::where('numero_compte', $number)->exists());

        return $number;
    }

    public function calculerSolde(Compte $compte): float
    {
        if ($compte->relationLoaded('transactions')) {
            $depots = $compte->transactions->where('type', 'depot')->where('statut', 'validee')->sum('montant');
            $retraits = $compte->transactions->where('type', 'retrait')->where('statut', 'validee')->sum('montant');
            $frais = $compte->transactions->where('type', 'frais')->where('statut', 'validee')->sum('montant');
        } else {
            $depots = Transaction::where('compte_id', $compte->id)->where('type', 'depot')->where('statut', 'validee')->sum('montant');
            $retraits = Transaction::where('compte_id', $compte->id)->where('type', 'retrait')->where('statut', 'validee')->sum('montant');
            $frais = Transaction::where('compte_id', $compte->id)->where('type', 'frais')->where('statut', 'validee')->sum('montant');
        }

        return $compte->solde_initial + $depots - $retraits - $frais;
    }

    private function findInArchives(string $id): ?Compte
    {
        try {
            $archivedData = DB::connection('pgsql_archive')
                ->table('archived_comptes')
                ->where('id', $id)
                ->where('type', 'epargne')
                ->first();

            if (!$archivedData) {
                return null;
            }

            $compte = new Compte((array) $archivedData);
            $compte->exists = true;

            return $compte;
        } catch (QueryException $e) {
            Log::warning('Failed to access archive database', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Recherche un compte par ID dans la base principale et les archives
     * Lance CompteNotFoundException si non trouvé
     * @throws CompteNotFoundException
     */
    public function findCompteById(string $id): Compte
    {
        // Validation UUID
        $this->validateUuid($id);

        // D'abord chercher dans la base principale
        $compte = Compte::find($id);
        if ($compte) {
            return $compte;
        }

        // Si pas trouvé, chercher dans les archives
        $compte = $this->findInArchives($id);
        if ($compte) {
            return $compte;
        }

        // Compte vraiment introuvable
        throw new CompteNotFoundException($id);
    }

    /**
     * Recherche un compte par numéro dans la base principale et les archives
     * Lance CompteNotFoundException si non trouvé
     * @throws CompteNotFoundException
     */
    public function findCompteByNumero(string $numero): Compte
    {
        // D'abord chercher dans la base principale
        $compte = Compte::where('numero_compte', $numero)->first();
        if ($compte) {
            return $compte;
        }

        // Si pas trouvé, chercher dans les archives
        try {
            $archivedData = DB::connection('pgsql_archive')
                ->table('archived_comptes')
                ->where('numero_compte', $numero)
                ->where('type', 'epargne')
                ->first();

            if ($archivedData) {
                $compte = new Compte((array) $archivedData);
                $compte->exists = true;
                return $compte;
            }
        } catch (QueryException $e) {
            Log::warning('Failed to access archive database for numero search', [
                'numero' => $numero,
                'error' => $e->getMessage()
            ]);
        }

        // Compte vraiment introuvable
        throw new CompteNotFoundException("numéro {$numero}", "Le compte avec le numéro '{$numero}' n'existe pas");
    }

    /**
     * Vérifie si un compte peut être modifié (n'est pas archivé)
     * Lance CompteArchivedException si le compte est archivé
     * @throws CompteArchivedException
     */
    public function ensureCompteIsModifiable(Compte $compte): void
    {
        if ($this->isArchivedCompte($compte)) {
            throw new CompteArchivedException($compte->id);
        }
    }

    /**
     * Vérifie si un numéro de compte existe déjà
     * Lance NumeroCompteAlreadyExistsException si le numéro existe
     * @throws NumeroCompteAlreadyExistsException
     */
    public function ensureNumeroCompteIsUnique(string $numeroCompte, ?string $excludeId = null): void
    {
        $query = Compte::where('numero_compte', $numeroCompte);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new NumeroCompteAlreadyExistsException($numeroCompte);
        }
    }

    /**
     * Vérifie si un compte est archivé
     */
    public function isArchivedCompte(Compte $compte): bool
    {
        return isset($compte->archived_at) || !$compte->exists;
    }
}
