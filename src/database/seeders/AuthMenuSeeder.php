<?php
namespace Database\Seeders;

use App\Models\Auth\Menu;
use App\Models\Auth\Module;
use Illuminate\Database\Seeder;
use App\Models\Auth\ModulePermission;

class AuthMenuSeeder extends Seeder
{
    private $modules;
    private $inserts = [];

    /**
     * run
     *
     * @return void
     */
    public function run()
    {
        $this->modules = Module::select('id', 'name')->get();

        Menu::truncate();

        $this->add('dashboard', 'Inicio', 1, 'fas fa-star', null);

        $this->add('admin', 'Admin', 10000, 'fas fa-gears', null);
        $this->add('admin.users', 'Usuarios', 100, 'fas fa-users', 'admin');
        $this->add('admin.roles', 'Roles', 200, 'fas fa-user-shield', 'admin');

        $inserts = collect($this->inserts)->map(function ($item) {
            unset($item['module']);

            return $item;
        })->toArray();

        Menu::insert($inserts);
    }

    private function add($aModule, $aName, $aOrder, $aIcon = null, $aParent = null)
    {
        $moduleId           = $this->modules->where('name', $aModule)->value('id');
        $permissionId       = 1; //index
        $modulePermissionid = ModulePermission::where('module_id', $moduleId)->where('permission_id', $permissionId)->value('id');

        $aParentId = null;
        if ($aParent) {
            $aParentId = collect($this->inserts)->search(function ($item, $index) use ($aParent) {
                return $item['module'] == $aParent;
            });
        }

        $this->inserts[] = [
            'parent_id'            => $aParentId ? ($aParentId + 1) : null,
            'module_permission_id' => $modulePermissionid,
            'name'                 => $aName,
            'order'                => $aOrder,
            'icon'                 => $aIcon,
            'module'               => $aModule,
        ];
    }
}
