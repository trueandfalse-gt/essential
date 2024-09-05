<?php
namespace Database\Seeders;

use App\Models\Auth\Module;
use Illuminate\Database\Seeder;
use App\Models\Auth\ModulePermission;

abstract class PermissionIds
{
    const INDEX  = 1;
    const REPORT = [1, 3];
    const ALL    = [1, 2, 3, 4, 5, 6, 7, 8];
}

class AuthModulesPermissionsSeeder extends Seeder
{
    private $modules;
    private $inserts = [];

    public function run()
    {
        $this->modules = Module::select('id', 'name')->get();

        ModulePermission::truncate();

        $this->add('dashboard', PermissionIds::INDEX);
        $this->add('admin.users', PermissionIds::ALL);
        $this->add('admin.roles', PermissionIds::ALL);

        // catalogs
        $this->add('catalogs.customers', PermissionIds::ALL);
        $this->add('catalogs.providers', PermissionIds::ALL);
        $this->add('catalogs.branches', PermissionIds::ALL);
        $this->add('catalogs.types_payments', PermissionIds::ALL);

        // inventory
        $this->add('inventory.catalogs.brands', PermissionIds::ALL);
        $this->add('inventory.catalogs.presentations', PermissionIds::ALL);
        $this->add('inventory.catalogs.categories', PermissionIds::ALL);
        $this->add('inventory.catalogs.products', PermissionIds::ALL);
        $this->add('inventory.adjustments.income', PermissionIds::ALL);
        $this->add('inventory.adjustments.outputs', PermissionIds::ALL);
        $this->add('inventory.purchases', PermissionIds::ALL);

        ModulePermission::insert($this->inserts);
    }

    private function add($aModule, $aPermissions)
    {
        if (!is_array($aPermissions)) {
            $aPermissions = [$aPermissions];
        }

        $moduleId = $this->modules->where('name', $aModule)->value('id');

        foreach ($aPermissions as $permissionId) {
            $this->inserts[] = ['module_id' => $moduleId, 'permission_id' => $permissionId];
        }
    }
}
