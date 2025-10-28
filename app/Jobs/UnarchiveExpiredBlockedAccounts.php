<?php

namespace App\Jobs;

use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnarchiveExpiredBlockedAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('🚀 Démarrage du job de désarchivage des comptes bloqués expirés');

        try {
            // Récupérer tous les comptes archivés dont la date de déblocage prévue est dépassée
            $archivedAccounts = DB::connection('pgsql_archive')
                ->table('archived_comptes')
                ->where('type', 'epargne')
                ->where('statut', 'bloque')
                ->where('datedeblocageprevue', '<', now())
                ->get();

            Log::info("📊 Nombre de comptes à désarchiver : {$archivedAccounts->count()}");

            foreach ($archivedAccounts as $archivedAccount) {
                DB::transaction(function () use ($archivedAccount) {
                    $this->unarchiveAccountAndTransactions($archivedAccount);
                });
            }

            Log::info('✅ Job de désarchivage terminé avec succès');
        } catch (\Throwable $th) {
            Log::error('❌ Erreur lors du désarchivage des comptes', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

    /**
     * Désarchive un compte et toutes ses transactions
     */
    private function unarchiveAccountAndTransactions($archivedAccount): void
    {
        Log::info("📦 Désarchivage du compte {$archivedAccount->numero_compte}");

        // Restaurer les transactions d'abord
        $archivedTransactions = DB::connection('pgsql_archive')
            ->table('archived_transactions')
            ->where('compte_id', $archivedAccount->id)
            ->get();

        $transactionsRestored = 0;
        foreach ($archivedTransactions as $transaction) {
            Transaction::create([
                'id' => $transaction->id,
                'compte_id' => $transaction->compte_id,
                'montant' => $transaction->montant,
                'type' => $transaction->type,
                'date_transaction' => $transaction->date_transaction,
                'devise' => $transaction->devise,
                'description' => $transaction->description,
                'statut' => $transaction->statut,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ]);
            $transactionsRestored++;
        }

        // Restaurer le compte (en utilisant restore() pour le soft delete)
        $compte = Compte::withTrashed()->find($archivedAccount->id);
        if (!$compte) {
            // Créer le compte s'il n'existe pas
            $compte = Compte::create([
                'id' => $archivedAccount->id,
                'client_id' => $archivedAccount->client_id,
                'numero_compte' => $archivedAccount->numero_compte,
                'titulaire' => $archivedAccount->titulaire,
                'type' => $archivedAccount->type,
                'solde_initial' => $archivedAccount->solde_initial,
                'devise' => $archivedAccount->devise,
                'date_creation' => $archivedAccount->date_creation,
                'statut' => 'actif', // Désarchiver comme actif
                'metadonnees' => $archivedAccount->metadonnees,
                'date_fermeture' => $archivedAccount->date_fermeture,
                'motifBlocage' => null, // Réinitialiser le blocage
                'dateBlocage' => null,
                'dateDeblocagePrevue' => null,
                'motifDeblocage' => 'Désarchivage automatique - période de blocage expirée',
                'dateDeblocage' => now(),
            ]);
        } else {
            // Restaurer le compte existant
            $compte->restore();
            $compte->update([
                'statut' => 'actif',
                'motifBlocage' => null,
                'dateBlocage' => null,
                'dateDeblocagePrevue' => null,
                'motifDeblocage' => 'Désarchivage automatique - période de blocage expirée',
                'dateDeblocage' => now(),
            ]);
        }

        // Supprimer les transactions de la base d'archivage
        DB::connection('pgsql_archive')
            ->table('archived_transactions')
            ->where('compte_id', $archivedAccount->id)
            ->delete();

        // Supprimer le compte de la base d'archivage
        DB::connection('pgsql_archive')
            ->table('archived_comptes')
            ->where('id', $archivedAccount->id)
            ->delete();

        Log::info("✅ Compte {$archivedAccount->numero_compte} désarchivé avec {$transactionsRestored} transactions");
    }
}
