<?php
namespace Database\Seeders;

use App\Models\Auth\Permission;
use Illuminate\Database\Seeder;

class AuthPermissionsSeeder extends Seeder
{
    private $inserts = [];
    /**
     * run
     *
     * @return void
     */
    public function run()
    {
        Permission::truncate();

        $this->add(1, 'index', 'Ver');
        $this->add(2, 'create', 'crear');
        $this->add(3, 'store', 'guardar', false);
        $this->add(4, 'edit', 'Editar');
        $this->add(5, 'update', 'Actualizar', false);
        $this->add(6, 'destroy', 'Borrar');
        $this->add(7, 'data', 'Obtener datos');
        $this->add(8, 'show', 'Ver detalle');

        Permission::insert($this->inserts);
    }

    private function add($aId, $aName, $afriendyName, $aShow = true)
    {
        $this->inserts[] = ['id' => $aId, 'name' => $aName, 'friendly_name' => $afriendyName, 'show' => $aShow];
    }
}
