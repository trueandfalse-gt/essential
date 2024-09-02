<?php
namespace Trueandfalse\Essential\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Trueandfalse\Essential\Models\Tenant\Tenant;

class TenantsSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:tenants {--t|id=*} {--class=DatabaseSeeder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeder tenant instances';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->option('id');

        $dbs = Tenant::when($id, function ($q) use ($id) {
            $q->whereIn('id', $id);
        })
            ->select('database_name', 'database_user', 'database_password')
            ->get();

        foreach ($dbs as $db) {
            $this->info("Seeding {$db->database_name}...");
            DB::purge('mysql');
            config()->set('database.connections.mysql.database', $db->database_name);
            config()->set('database.connections.mysql.username', $db->database_user);
            config()->set('database.connections.mysql.password', $db->database_password);
            DB::reconnect('mysql');

            $this->call('db:seed', ['--class' => $this->option('class')]);
        }

        $this->info("Seed completed.");

    }
}
