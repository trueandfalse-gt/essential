<?php
namespace Trueandfalse\Essential\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use App\Models\Auth\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Trueandfalse\Essential\Http\Controllers\EssenCrudController;

class UsersController extends EssenCrudController
{
    public function __construct()
    {
        $this->setTitle('Usuarios');
        $this->setModel(new User());
        $this->setField(['name' => trans('Nombre'), 'field' => 'name', 'validate' => 'required']);
        $this->setField(['name' => trans('Email'), 'field' => 'email', 'validate' => 'required']);

        $this->setPermissions('admin.users');
    }

    public function create(Request $request)
    {
        return $this->edit($request, 0);
    }

    public function edit(Request $request, $rowId)
    {
        if ($rowId != 0) {
            $record = User::with('roles:user_id,role_id')
                ->select('id', 'name', 'email')
                ->findOrFail($rowId);

            $record->user_roles            = $record->roles->pluck('role_id')->toArray();
            $record->password              = null;
            $record->password_confirmation = null;
        } else {
            $record = [
                'id'                    => 0,
                'name'                  => null,
                'email'                 => null,
                'password'              => null,
                'password_confirmation' => null,
                'user_roles'            => [],
            ];
        }

        $caption = $rowId != 0 ? trans('Editar') : trans('Nuevo');
        $roles   = Role::select('id AS value', 'name AS label')->orderBy('name')->get();
        $props   = [
            'title'   => 'Usuarios',
            'caption' => $caption,
            'record'  => $record,
            'roles'   => $roles,
        ];

        return Inertia::render('Essen::Users/Edit', $props);
    }

    public function store(Request $request)
    {
        return $this->update($request, 0);
    }

    public function update(Request $request, $rowId)
    {
        $request->validate([
            'name'                  => 'required',
            'email'                 => 'required',
            'user_roles'            => 'required|array',
            'password'              => 'nullable|min:6',
            'password_confirmation' => 'nullable|min:6|required_with:password|same:password',
        ]);

        if ($rowId == 0) {
            $user = new User;
        } else {
            $user = User::find($rowId);
        }

        DB::transaction(function () use ($request, $user) {
            $user->name  = $request->name;
            $user->email = $request->email;

            if ($request->password) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            UserRole::where('user_id', $user->id)->whereNotIn('role_id', $request->user_roles)->delete();
            foreach ($request->user_roles as $rolId) {
                UserRole::firstOrCreate([
                    'user_id' => $user->id,
                    'role_id' => $rolId,
                ]);
            }
        });

        return response()->json(['message' => trans('Registro almacenado correctamente.')]);
    }
}
