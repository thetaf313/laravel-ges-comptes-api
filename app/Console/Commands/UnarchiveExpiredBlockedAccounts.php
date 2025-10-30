<?php

namespace App\Console\Commands;

use App\Jobs\UnarchiveExpiredBlockedAccounts as UnarchiveJob;
use Illuminate\Console\Command;

class UnarchiveExpiredBlockedAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comptes:unarchive-expired-blocked {--sync : Exécuter immédiatement sans queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Désarchiver les comptes épargne bloqués dont la date de fin de blocage est échue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Démarrage du désarchivage des comptes bloqués expirés...');

        if ($this->option('sync')) {
            // Exécuter immédiatement
            $job = new UnarchiveJob();
            $job->handle();
            $this->info('Désarchivage terminé (exécution synchrone).');
        } else {
            // Dispatch en queue
            UnarchiveJob::dispatch();
            $this->info('Job de désarchivage dispatché en queue.');
        }

        return Command::SUCCESS;
    }
}
