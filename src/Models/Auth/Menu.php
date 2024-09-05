<?php
namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'auth_menu';

    public function modulePermission()
    {
        return $this->hasOne(ModulePermission::class, 'id', 'module_permission_id');
    }
}
