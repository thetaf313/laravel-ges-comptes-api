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
        Log::info('Démarrage du désarchivage des comptes bloqués expirés');

        // Récupérer les enregistrements archivés dont la date de fin de blocage est échue
        // Récupérer tous les enregistrements archivés (on filtrera en PHP pour éviter
        // les problèmes de casse des colonnes sur Postgres/archived DB)
        $archived = DB::connection('pgsql_archive')->table('archived_comptes')
            ->where('type', 'epargne')
            ->get();

        $unarchivedCount = 0;

        foreach ($archived as $row) {
            try {
                // Vérifier la dateDeblocagePrevue (prendre en compte la casse possible)
                $rowDate = $row->datedeblocageprevue ?? $row->dateDeblocagePrevue ?? null;
                if (!$rowDate || Carbon::parse($rowDate)->gt(Carbon::now())) {
                    // Pas encore arrivé à échéance
                    continue;
                }

                // Recréer l'enregistrement dans la table comptes
                $data = [
                    'id' => $row->id,
                    'client_id' => $row->client_id,
                    'numero_compte' => $row->numero_compte,
                    'titulaire' => $row->titulaire,
                    'type' => $row->type,
                    'solde_initial' => $row->solde_initial,
                    'devise' => $row->devise,
                    'date_creation' => $row->date_creation,
                    'statut' => 'actif', // Forcer le statut à actif car le blocage a expiré
                    'metadonnees' => $row->metadonnees,
                    'date_fermeture' => $row->date_fermeture,
                    'motifBlocage' => $row->motifblocage,
                    'dateBlocage' => $row->dateblocage,
                    'dateDeblocagePrevue' => $row->datedeblocageprevue,
                    'motifDeblocage' => 'Blocage expiré automatiquement', // Motif automatique
                    'dateDeblocage' => Carbon::now(), // Date de déblocage actuelle
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                // Insertion via la connexion par défaut
                DB::table('comptes')->insert($data);

                // Supprimer de la table d'archive
                DB::connection('pgsql_archive')->table('archived_comptes')->where('id', $row->id)->delete();

                Log::info('Compte désarchivé', ['compte_id' => $row->id]);
                $unarchivedCount++;
            } catch (\Exception $e) {
                Log::error('Erreur lors du désarchivage du compte', [
                    'compte_id' => $row->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Désarchivage terminé', ['comptes_desarchives' => $unarchivedCount]);
    }
}
