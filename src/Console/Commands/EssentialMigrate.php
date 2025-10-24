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
    protected $signature = 'essential:migrate-tenants';

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
        $path    = 'database/migrations/tenants';
        $options = [
            '--path'     => $path,
            '--database' => config('database.connections.tenants.database'),
        ];

        $this->call('migrate', $options);
        $this->info("Migration completed.");
    }
}
