<?php
namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class RolModulePermission extends Model
{
    protected $table   = 'auth_role_module_permissions';
    protected $guarded = ['id'];

    public $timestamps = false;
}
