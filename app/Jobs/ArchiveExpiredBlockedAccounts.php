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

class ArchiveExpiredBlockedAccounts implements ShouldQueue
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
        Log::info('🚀 Démarrage du job d\'archivage des comptes bloqués expirés');

        try {
            // Trouver tous les comptes épargne bloqués dont la date de déblocage prévue est dépassée
            $expiredBlockedAccounts = Compte::where('type', 'epargne')
                ->where('statut', 'bloque')
                ->where('dateDeblocagePrevue', '<', now())
                ->with('transactions')
                ->get();

            Log::info("📊 Nombre de comptes à archiver : {$expiredBlockedAccounts->count()}");

            foreach ($expiredBlockedAccounts as $compte) {
                DB::transaction(function () use ($compte) {
                    $this->archiveAccountAndTransactions($compte);
                });
            }

            Log::info('✅ Job d\'archivage terminé avec succès');
        } catch (\Throwable $th) {
            Log::error('❌ Erreur lors de l\'archivage des comptes', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

    /**
     * Archive un compte et toutes ses transactions
     */
    private function archiveAccountAndTransactions(Compte $compte): void
    {
        Log::info("📦 Archivage du compte {$compte->numero_compte}");

        // Archiver les transactions d'abord
        $transactionsArchived = 0;
        foreach ($compte->transactions as $transaction) {
            DB::connection('pgsql_archive')->table('archived_transactions')->insert([
                'id' => $transaction->id,
                'compte_id' => $transaction->compte_id,
                'montant' => $transaction->montant,
                'type' => $transaction->type,
                'date_transaction' => $transaction->date_transaction,
                'devise' => $transaction->devise,
                'description' => $transaction->description,
                'statut' => $transaction->statut,
                'archived_at' => now(),
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ]);
            $transactionsArchived++;
        }

        // Archiver le compte
        DB::connection('pgsql_archive')->table('archived_comptes')->insert([
            'id' => $compte->id,
            'client_id' => $compte->client_id,
            'numero_compte' => $compte->numero_compte,
            'titulaire' => $compte->titulaire,
            'type' => $compte->type,
            'solde_initial' => $compte->solde_initial,
            'devise' => $compte->devise,
            'date_creation' => $compte->date_creation,
            'statut' => $compte->statut,
            'metadonnees' => json_encode($compte->metadonnees),
            'date_fermeture' => $compte->date_fermeture,
            'motifblocage' => $compte->motifBlocage,
            'dateblocage' => $compte->dateBlocage,
            'datedeblocageprevue' => $compte->dateDeblocagePrevue,
            'motifdeblocage' => $compte->motifDeblocage,
            'datedeblocage' => $compte->dateDeblocage,
            'archived_at' => now(),
            'created_at' => $compte->created_at,
            'updated_at' => $compte->updated_at,
        ]);

        // Supprimer les transactions de la base principale
        Transaction::where('compte_id', $compte->id)->delete();

        // Supprimer le compte de la base principale (soft delete)
        $compte->delete();

        Log::info("✅ Compte {$compte->numero_compte} archivé avec {$transactionsArchived} transactions");
    }
}
