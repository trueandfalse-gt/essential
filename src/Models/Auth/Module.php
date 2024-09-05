<?php
namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table      = 'auth_modules';
    protected $primaryKey = 'id';

    public function modulePermissions()
    {
        return $this->hasMany(ModulePermission::class, 'module_id', 'id');
    }
}
