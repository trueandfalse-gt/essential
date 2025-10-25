<?php
namespace Trueandfalse\Essential\Console\Commands;

use Illuminate\Console\Command;

class EssentialMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'essential:migrate-tenants {--force} {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate tenants';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isRollback = $this->option('rollback');
        $path       = 'database/migrations/tenants';
        $options    = [
            '--force'    => $this->option('force'),
            '--path'     => $path,
            '--database' => config('database.connections.tenants.database'),
        ];

        if (!$isRollback) {
            $this->call('migrate:', $options);
        } else {
            $this->call('migrate:rollback', $options);

        }
        $this->info("Migration completed.");
    }
}
