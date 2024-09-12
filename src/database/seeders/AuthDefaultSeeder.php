<?php
namespace Database\Seeders;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Auth\UserRole;
use Illuminate\Database\Seeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\AuthAdminSeeder;
use Illuminate\Support\Facades\Schema;

class AuthDefaultSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        Role::insert([
            [
                'id'   => 1,
                'name' => 'Admin Dev',
            ],
            [
                'id'   => 2,
                'name' => 'Administrador',
            ],
        ]);

        User::insert([
            [
                'id'       => 1,
                'email'    => 'dev@dev.com',
                'password' => '$2y$10$zBEVSjGinu16wBbE4sJqqOBRlkaEJ/qRnGEtiju5xIacT.GMdmG..', //Dev$$..
                'name' => 'dev',
            ],
        ]);

        UserRole::insert([
            [
                'id'      => 1,
                'user_id' => 1,
                'role_id' => 1,
            ],
        ]);

        Schema::enableForeignKeyConstraints();

        $AS = new AuthAdminSeeder;
        $AS->run();

        $DB = new DatabaseSeeder;
        $DB->run();
    }
}
