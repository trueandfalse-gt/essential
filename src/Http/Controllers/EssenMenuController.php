<?php
namespace Trueandfalse\Essential\Http\Controllers;

use App\Models\Auth\Menu;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Models\Auth\RolModulePermission;

class EssenMenuController extends Controller
{
    private static $dataMenu;
    private static $menu = [];

    public static function build()
    {
        $menu = Cache::rememberForever('menu', function () {
            $roles                   = auth()->user()->roles->pluck('role_id');
            $roleModulePermissionIds = RolModulePermission::query()
                ->select('module_permission_id')
                ->whereIn('role_id', $roles)
                ->distinct('module_permission_id')
                ->pluck('module_permission_id')
                ->toArray();

            self::$dataMenu = Menu::query()
                ->with([
                    'modulePermission:id,module_id,permission_id',
                    'modulePermission.module:id,name',
                    'modulePermission.permission:id,name',
                ])
                ->select('id', 'parent_id', 'module_permission_id', 'name', 'order', 'icon')
                ->whereNull('module_permission_id')
                ->orWhereIn('module_permission_id', $roleModulePermissionIds)
                ->orderBy('order')
                ->get()
                ->map(function ($menu) {
                    $module = optional(optional($menu->modulePermission)->module)->name;

                    return (Object) [
                        'id'        => $menu->id,
                        'parent_id' => $menu->parent_id,
                        'name'      => trans($menu->name),
                        'order'     => $menu->order,
                        'icon'      => $menu->icon,
                        'module'    => $module,
                        'url'       => str_replace('.', '/', $module),
                    ];
                });

            self::menuBuild();

            return collect(self::$menu)->values();
        });

        return $menu;

    }

    private static function menuBuild()
    {
        $items = self::$dataMenu->whereNull('parent_id');

        foreach ($items as $menu) {
            self::$menu[$menu->id]           = $menu;
            self::$menu[$menu->id]->children = self::children($menu->id);
        }
    }

    private static function children($parentId)
    {
        $items = self::$dataMenu->where('parent_id', $parentId);

        $arr = [];
        foreach ($items as $menu) {
            $arr[$menu->id]           = $menu;
            $arr[$menu->id]->children = self::children($menu->id);
        }

        return $arr;
    }
}
