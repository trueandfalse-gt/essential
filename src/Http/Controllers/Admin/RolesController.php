<?php
namespace Trueandfalse\Essential\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Auth\Role;
use App\Models\Auth\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Auth\ModulePermission;
use App\Models\Auth\RolModulePermission;
use Trueandfalse\Essential\Traits\EssenTrait;

class RolesController extends EssenCrudController
{
    use EssenTrait;

    public function __construct()
    {
        $this->setVue();
        $this->setModel(new Role());
        $this->setField(['name' => 'Nombre', 'field' => 'name', 'validate' => 'required']);
        $this->setField(['name' => 'Descripción', 'field' => 'description']);

        $this->setPermissions('admin.roles');
    }

    public function index(Request $request)
    {
        $columns = $this->getColumns();
        $props   = [
            'title'       => 'Roles',
            'columns'     => $this->columnsConvert($columns),
            'permissions' => $this->getPermissions(),
        ];

        return Inertia::render('Essen::Module/Index', $props);
    }

    public function edit(Request $request, $rowId)
    {
        if ($rowId != 0) {
            $record      = Role::findOrFail($rowId);
            $permissions = $record->modulePermissions->pluck('module_permission_id');
        } else {
            $record = [
                'id'               => 0,
                'name'             => null,
                'description'      => null,
                'modulePermission' => [],
            ];
            $permissions = [];
        }

        $caption      = $rowId != 0 ? 'Editar' : 'Nuevo';
        $columns      = $this->getColumns();
        $moduleGroups = Module::with([
            'modulePermissions.permission:id,friendly_name,show',
        ])
            ->orderBy('name')
            ->where('show', true)
            ->get()
            ->each(function ($module) {
                $arr = explode(' - ', $module->friendly_name);

                $module->group       = count($arr) > 1 ? reset($arr) : '';
                $module->permissions = $module->modulePermissions->filter(function ($modulePermission) {
                    return $modulePermission->permission->show == true;
                })->values();
            })
            ->sortBy('group')
            ->groupBy('group');

        $props = [
            'title'            => 'Roles',
            'caption'          => $caption,
            'fields'           => $this->columnsConvert($columns),
            'record'           => $record,
            'role_permissions' => $permissions,
            'module_groups'    => $moduleGroups,
        ];

        return Inertia::render('Essen::Roles/Edit', $props);
    }

    public function store(Request $request)
    {
        return $this->update($request, 0);
    }

    public function update(Request $request, $rowId)
    {
        $request->validate([
            'name' => 'required',
        ]);

        if ($rowId == 0) {
            $role = Role::firstOrNew(['nombre' => $request->nombre]);
        } else {
            $role = Role::find($rowId);
        }

        $modulePermissions = ModulePermission::with('permission:id')->select('id', 'module_id', 'permission_id')->get();
        try {
            DB::transaction(function () use ($request, $role, $modulePermissions) {
                $role->name        = $request->name;
                $role->description = $request->description;
                $role->save();

                $roleId = $role->id;
                RolModulePermission::where('role_id', $roleId)->delete();
                if ($request->module_permissions) {
                    foreach ($request->module_permissions as $id) {
                        $modulePermission = $modulePermissions->where('id', $id)->first();

                        $roleModuloPermiso = RolModulePermission::firstOrNew(['role_id' => $roleId, 'module_permission_id' => $id]);
                        $roleModuloPermiso->save();

                        $data = $modulePermissions
                            ->where('module_id', $modulePermission->module_id)
                            ->where('permission_id', 7)
                            ->first();

                        if ($modulePermission->permission_id == 1 && $data) {
                            $roleModuloPermiso = RolModulePermission::firstOrNew([
                                'role_id'              => $roleId,
                                'module_permission_id' => $data->id,
                            ]);
                            $roleModuloPermiso->save();

                            continue;
                        }

                        $store = $modulePermissions
                            ->where('module_id', $modulePermission->module_id)
                            ->where('permission_id', 3)
                            ->first();

                        if ($modulePermission->permission_id == 2 && $store) {
                            $roleModuloPermiso = RolModulePermission::firstOrNew([
                                'role_id'              => $roleId,
                                'module_permission_id' => $store->id,
                            ]);
                            $roleModuloPermiso->save();

                            continue;
                        }

                        $update = $modulePermissions
                            ->where('module_id', $modulePermission->module_id)
                            ->where('permission_id', 5)
                            ->first();

                        if ($modulePermission->permission_id == 4 && $update) {
                            $rolModuloPermiso = RolModulePermission::firstOrNew([
                                'role_id'              => $roleId,
                                'module_permission_id' => $update->id,
                            ]);
                            $rolModuloPermiso->save();
                        }
                    }
                }
            });
        } catch (QueryExecption $e) {
            abort(406, 'Error al guardar el registro.');
        }

        return response()->json(['message' => 'Registro almacenado correctamente.']);

    }

    public function destroy(Request $request, $rowId)
    {
        if (in_array($rowId, [1, 2])) {
            abort(422, 'Registro protegido, no es posible la eliminación.');
        }

        Role::find($rowId)->delete();

        return response()->json([
            'message' => 'Registro eliminado correctamente.',
        ]);
    }
}
