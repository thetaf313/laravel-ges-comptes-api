<?php

namespace App\Console\Commands;

use App\Jobs\ArchiveExpiredBlockedAccounts as ArchiveJob;
use Illuminate\Console\Command;

class ArchiveExpiredBlockedAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comptes:archive-expired-blocked {--sync : Exécuter immédiatement sans queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archiver les comptes épargne bloqués dont la date de début de blocage est échue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Démarrage de l\'archivage des comptes bloqués expirés...');

        if ($this->option('sync')) {
            // Exécuter immédiatement
            $job = new ArchiveJob();
            $job->handle();
            $this->info('Archivage terminé (exécution synchrone).');
        } else {
            // Dispatch en queue
            ArchiveJob::dispatch();
            $this->info('Job d\'archivage dispatché en queue.');
        }

        return Command::SUCCESS;
    }
}
