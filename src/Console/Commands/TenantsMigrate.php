<?php
namespace Trueandfalse\Essential\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Trueandfalse\Essential\Models\Tenant\Tenant;

class TenantsMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:tenants {--t|id=*} {--force} {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate tenant intances';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id         = $this->option('id');
        $isRollback = $this->option('rollback');

        $dbs = Tenant::when($id, function ($q) use ($id) {
            $q->whereIn('id', $id);
        })
            ->select('database_name', 'database_user', 'database_password')
            ->get();

        foreach ($dbs as $db) {
            $this->info("Migrating {$db->database_name}...");
            DB::purge('mysql');
            config()->set('database.connections.mysql.database', $db->database_name);
            config()->set('database.connections.mysql.username', $db->database_user);
            config()->set('database.connections.mysql.password', $db->database_password);
            DB::reconnect('mysql');

            if (!$isRollback) {
                $this->call('migrate', ['--force' => $this->option('force')]);
            } else {
                $this->call('migrate:rollback', ['--force' => $this->option('force')]);
            }
        }

        $this->info("Migration completed.");
    }
}
