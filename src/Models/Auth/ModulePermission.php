<?php
namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class ModulePermission extends Model
{
    protected $table = 'auth_module_permissions';

    public function module()
    {
        return $this->hasOne(Module::class, 'id', 'module_id');
    }

    public function permission()
    {
        return $this->hasOne(Permission::class, 'id', 'permission_id');
    }
}
