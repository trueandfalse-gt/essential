<?php
namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\RolModulePermission;

class Role extends Model
{
    protected $table   = 'auth_roles';
    public $timestamps = false;

    public function modulePermissions()
    {
        return $this->hasMany(RolModulePermission::class, 'role_id', 'id');
    }
}
