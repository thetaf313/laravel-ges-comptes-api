<?php

namespace App\Console\Commands;

use App\Jobs\ArchiveExpiredBlockedAccounts;
use Illuminate\Console\Command;

class ArchiveExpiredAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:archive-expired-accounts {--sync : Exécuter immédiatement sans queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archiver les comptes épargne bloqués dont la période de blocage est expirée';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Lancement de l\'archivage des comptes bloqués expirés...');

        if ($this->option('sync')) {
            // Exécuter immédiatement
            $this->info('⚡ Exécution synchrone...');
            ArchiveExpiredBlockedAccounts::dispatchSync();
        } else {
            // Dispatch en queue
            $this->info('📋 Dispatch en file d\'attente...');
            ArchiveExpiredBlockedAccounts::dispatch();
        }

        $this->info('✅ Commande d\'archivage lancée avec succès!');
        $this->comment('Utilisez --sync pour exécuter immédiatement sans queue.');
    }
}
