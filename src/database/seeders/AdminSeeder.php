<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        DB::statement('DELETE FROM auth_role_module_permissions WHERE role_id = 1');
        DB::statement('INSERT INTO auth_role_module_permissions (role_id, module_permission_id, created_at, updated_at) SELECT 1, id, NOW(), NOW() FROM auth_module_permissions');

        Schema::enableForeignKeyConstraints();
    }
}
