<?php
namespace Trueandfalse\Essential\Http\Controllers;

use DB;
use Route;
use Illuminate\Http\Request;
use App\Models\Auth\Permission;
use App\Models\Auth\ModulePermission;
use App\Models\Auth\RolModulePermission;

class EssenAccessController
{
    public static function accessRoute(Request $request, $routeName)
    {
        $exist = Route::has($routeName);

        if (!$exist) {
            abort(404, 'Page not found');
        }

        $arr            = explode('.', $routeName);
        $permissionName = array_pop($arr);
        $moduleName     = implode('.', $arr);

        $module = ModulePermission::select('m.id')
            ->leftJoin('auth_modules as m', 'auth_module_permissions.module_id', 'm.id')
            ->where('m.name', $moduleName)
            ->first();

        if (!$module) {
            abort(404, 'Module not found');
        }

        $rolesIds         = auth()->user()->roles->pluck('role_id');
        $modulePermission = RolModulePermission::select(
            'm.name as module',
            'p.name as permission',
            DB::raw('CONCAT(m.name, ".", p.name) as route_name'))
            ->leftJoin('auth_module_permissions as mp', 'mp.id', 'auth_role_module_permissions.module_permission_id')
            ->leftJoin('auth_modules as m', 'mp.module_id', 'm.id')
            ->leftJoin('auth_permissions as p', 'mp.permission_id', 'p.id')
            ->whereIn('role_id', $rolesIds)
            ->where('m.id', $module->id)
            ->where('p.name', $permissionName)
            ->first();

        if (!$modulePermission) {
            abort(404, 'unauthorized action');
        }

        return response()->json($modulePermission->route_name);
    }

    public static function modulePermissions($module, $permissionNames = null)
    {
        $permissions = new Permission();

        if ($permissionNames) {
            $permissions = $permissions->whereIn('name', $permissionNames);
        }

        $modulePermissions = ModulePermission::select('p.name')
            ->leftJoin('auth_modules as m', 'auth_module_permissions.module_id', 'm.id')
            ->leftJoin('auth_permissions as p', 'auth_module_permissions.permission_id', 'p.id')
            ->where('m.name', $module)
            ->get();

        $permissions = $permissions
            ->pluck('name')
            ->mapWithKeys(function ($permissionName) use ($modulePermissions) {
                $exist = $modulePermissions->where('name', $permissionName)->first();
                if ($exist) {
                    $value = true;
                } else {
                    $value = false;
                }

                return [$permissionName => $value];
            });

        return $permissions;
    }
}
