<?php
namespace Database\Seeders;

use App\Models\Auth\Module;
use Illuminate\Database\Seeder;

abstract class Permissions
{
    const ALL    = [1, 2, 3, 4];
    const SHOW   = [2];
    const REPORT = [2, 3];
}

class AuthModulesSeeder extends Seeder
{
    private $inserts = [];

    /**
     * run
     *
     * @return void
     */
    public function run()
    {
        Module::truncate();

        $this->add(1, 'dashboard', 'Dashboard');
        $this->add(2, 'admin.users', 'Admin - Usuarios');
        $this->add(3, 'admin.roles', 'Admin - Roles');

        Module::insert($this->inserts);
    }

    private function add($aId, $aName, $afriendyName, $aShow = true)
    {
        $this->inserts[] = ['id' => $aId, 'name' => $aName, 'friendly_name' => $afriendyName, 'show' => $aShow];
    }
}
