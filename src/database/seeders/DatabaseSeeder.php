<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        $this->call(AuthPermissionsSeeder::class);
        $this->call(AuthModulesSeeder::class);
        $this->call(AuthModulesPermissionsSeeder::class);
        $this->call(AuthMenuSeeder::class);
        $this->call(AdminSeeder::class);
        Schema::enableForeignKeyConstraints();

    }
}
