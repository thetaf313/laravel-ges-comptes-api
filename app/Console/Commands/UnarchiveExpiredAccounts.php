<?php

namespace App\Console\Commands;

use App\Jobs\UnarchiveExpiredBlockedAccounts;
use Illuminate\Console\Command;

class UnarchiveExpiredAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:unarchive-expired-accounts {--sync : Exécuter immédiatement sans queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Désarchiver les comptes épargne bloqués dont la période de blocage est expirée';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Lancement du désarchivage des comptes bloqués expirés...');

        if ($this->option('sync')) {
            // Exécuter immédiatement
            $this->info('⚡ Exécution synchrone...');
            UnarchiveExpiredBlockedAccounts::dispatchSync();
        } else {
            // Dispatch en queue
            $this->info('📋 Dispatch en file d\'attente...');
            UnarchiveExpiredBlockedAccounts::dispatch();
        }

        $this->info('✅ Commande de désarchivage lancée avec succès!');
        $this->comment('Utilisez --sync pour exécuter immédiatement sans queue.');
    }
}
