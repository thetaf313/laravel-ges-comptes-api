<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreateArchiveTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createArchivedComptesTable();
        $this->createArchivedTransactionsTable();
    }

    private function createArchivedComptesTable(): void
    {
        DB::connection('pgsql_archive')->statement("
            CREATE TABLE IF NOT EXISTS archived_comptes (
                id UUID PRIMARY KEY,
                client_id UUID NOT NULL,
                numero_compte VARCHAR(255) NOT NULL,
                titulaire VARCHAR(255) NOT NULL,
                type VARCHAR(255) CHECK (type IN ('epargne', 'cheque')),
                solde_initial DECIMAL(15,2) NOT NULL,
                devise VARCHAR(10) DEFAULT 'XOF',
                date_creation DATE NOT NULL,
                statut VARCHAR(255) CHECK (statut IN ('actif', 'bloque', 'ferme')),
                metadonnees JSONB,
                date_fermeture TIMESTAMP,
                motifBlocage VARCHAR(255),
                dateBlocage TIMESTAMP,
                dateDeblocagePrevue TIMESTAMP,
                motifDeblocage VARCHAR(255),
                dateDeblocage TIMESTAMP,
                archived_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            )
        ");
    }

    private function createArchivedTransactionsTable(): void
    {
        DB::connection('pgsql_archive')->statement("
            CREATE TABLE IF NOT EXISTS archived_transactions (
                id UUID PRIMARY KEY,
                compte_id UUID NOT NULL,
                montant DECIMAL(15,2) NOT NULL,
                type VARCHAR(255) CHECK (type IN ('depot', 'retrait', 'virement', 'frais')),
                date_transaction DATE NOT NULL,
                devise VARCHAR(3) DEFAULT 'XOF',
                description TEXT,
                statut VARCHAR(255) DEFAULT 'en_attente' CHECK (statut IN ('en_attente', 'validee', 'annulee')),
                archived_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            )
        ");
    }
}
