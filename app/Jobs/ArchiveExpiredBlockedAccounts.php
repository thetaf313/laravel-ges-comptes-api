<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        Log::info('Démarrage de l\'archivage des comptes bloqués expirés');

        // 1) Activer les blocages programmés : comptes de type épargne actifs dont la dateBlocage est atteinte
        $toActivate = Compte::where('type', 'epargne')
            ->where('statut', 'actif')
            ->whereNotNull('dateBlocage')
            ->where('dateBlocage', '<=', Carbon::now())
            ->get();

        foreach ($toActivate as $compte) {
            try {
                $compte->update(['statut' => 'bloque']);
                Log::info('Activation du blocage programmé', ['compte_id' => $compte->id, 'dateBlocage' => $compte->dateBlocage]);
            } catch (\Exception $e) {
                Log::error('Erreur activation blocage', ['compte_id' => $compte->id, 'error' => $e->getMessage()]);
            }
        }

        // 2) Archiver les comptes bloqués dont la date de fin de blocage est échue (dateDeblocagePrevue <= now)
        $comptesToArchive = Compte::where('type', 'epargne')
            ->where('statut', 'bloque')
            ->whereNotNull('dateDeblocagePrevue')
            ->where('dateDeblocagePrevue', '<=', Carbon::now())
            ->get();

        $archivedCount = 0;

        foreach ($comptesToArchive as $compte) {
            try {
                // Préparer le payload pour la table d'archive
                $payload = [
                    'id' => $compte->id,
                    'client_id' => $compte->client_id,
                    'numero_compte' => $compte->numero_compte,
                    'titulaire' => $compte->titulaire,
                    'type' => $compte->type,
                    'solde_initial' => $compte->solde_initial,
                    'devise' => $compte->devise,
                    'date_creation' => $compte->date_creation?->toDateString(),
                    'statut' => $compte->statut,
                    'metadonnees' => json_encode($compte->metadonnees ?? []),
                    'date_fermeture' => $compte->date_fermeture?->toDateTimeString(),
                    'motifblocage' => $compte->motifBlocage,
                    'dateblocage' => $compte->dateBlocage?->toDateTimeString(),
                    'datedeblocageprevue' => $compte->dateDeblocagePrevue?->toDateTimeString(),
                    'motifdeblocage' => $compte->motifDeblocage,
                    'datedeblocage' => $compte->dateDeblocage?->toDateTimeString(),
                    'archived_at' => Carbon::now()->toDateTimeString(),
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ];

                // Insérer dans la base d'archive
                DB::connection('pgsql_archive')->table('archived_comptes')->insert($payload);

                // Supprimer le compte d'origine (hard delete pour archivage permanent)
                $compte->forceDelete();

                Log::info('Compte archivé', ['compte_id' => $compte->id]);
                $archivedCount++;
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'archivage du compte', [
                    'compte_id' => $compte->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Archivage terminé', ['comptes_archives' => $archivedCount]);
    }
}
