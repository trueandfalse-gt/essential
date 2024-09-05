<?php
namespace Trueandfalse\Essential\Http\Controllers;

use DB;
use Route;
use Illuminate\Http\Request;
use App\Models\Auth\ModulePermission;
use App\Models\Auth\RolModulePermission;

class EssenAccessController
{
    public static function accessRoute(Request $request, $route)
    {
        $routeFound = collect(Route::getRoutes()->get())
            ->filter(function ($item) use ($route) {
                return $item->getName() == $route;
            })->first();

        if (!$routeFound) {
            abort(404, 'Page not found');
        }

        $arr = explode('.', $route);
        array_pop($arr);
        $routeModule = implode('.', $arr);

        $moduloFound = ModulePermission::select('m.name as module', 'm.id AS module_id')
            ->leftJoin('auth_modules as m', 'auth_module_permissions.module_id', 'm.id')
            ->where('m.name', $routeModule)
            ->first();

        if (!$moduloFound) {
            abort(404, 'Module not found');
        }

        $userRoles         = auth()->user()->roles->pluck('role_id');
        $modulePermissions = RolModulePermission::select(
            'm.name as module',
            'p.name as permission',
            DB::raw('CONCAT(m.name, ".", p.name) as route'))
            ->leftJoin('auth_module_permissions as mp', 'mp.id', 'auth_role_module_permissions.module_permission_id')
            ->leftJoin('auth_modules as m', 'mp.module_id', 'm.id')
            ->leftJoin('auth_permissions as p', 'mp.permission_id', 'p.id')
            ->whereIn('role_id', $userRoles)
            ->where('m.id', $moduloFound->module_id)
            ->get();

        $permisoFound = $modulePermissions->where('route', $route)->first();

        if (!$permisoFound) {
            abort(404, 'unauthorized action');
        }

        return response()->json($permisoFound->route);
    }
}
