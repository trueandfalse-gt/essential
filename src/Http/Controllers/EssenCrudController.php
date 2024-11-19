<?php
namespace Trueandfalse\Essential\Http\Controllers;

use Hash;
use Crypt;
use Exception;
use \Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Trueandfalse\Essential\Http\Controllers\EssenAccessController;

class EssenCrudController extends Controller
{
    private $vue              = false;
    private $module           = null;
    private $exports          = [];
    private $component        = 'crud-index-component';
    private $title            = '';
    private $model            = null;
    private $fields           = [];
    private $permissions      = [];
    private $permissionsExtra = [];
    private $destroy          = false;
    private $buttonActions    = [];
    private $listWhere        = [];
    private $listWhereIn      = [];
    private $listOrderBy      = [];
    private $listJoin         = [];
    private $listHidden       = [];
    private $enabledFirstOr   = false;
    private $fiedsFirstOr     = [];
    private $options          = [];

    /**
     * index
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        if (!$request->responseJson) {
            if (!$this->model) {
                if ($request->expectsJson()) {
                    abort(406, 'setModel required');
                }
                dd('setModel required');

            }

            $columns      = $this->getColumns(true);
            $orders       = Collect($this->listOrderBy);
            $orderColumns = [];
            foreach ($columns as $index => $arr) {
                $orderFound = $orders->first(function ($item) use ($arr) {return $item['field'] == $arr['field'];});
                if ($orderFound) {
                    $orderColumns[] = [$index, $orderFound['direction']];
                }
            }

            if ($this->vue == true) {
                $dataView = [
                    'component' => $this->component,
                    'dataVue'   => [
                        'pageTitle'     => $this->title,
                        'route'         => $request->route()->uri(),
                        'columns'       => $columns,
                        'permissions'   => $this->permissions,
                        'orderColumns'  => $orderColumns,
                        'buttonActions' => $this->buttonActions,
                        'exports'       => $this->exports,
                        'options'       => (Object) $this->options,
                        'request'       => $request->all(),
                    ],
                ];

                return view(config('hcode.crud.view.backendIndex'), $dataView);
            }

            $dataView = [
                'pageTitle'     => $this->title,
                'route'         => $request->route()->getName(),
                'columns'       => $columns,
                'permissions'   => $this->permissions,
                'orderColumns'  => $orderColumns,
                'buttonActions' => $this->buttonActions,
                'request'       => $request->all(),
            ];

            return view(config('hcode.crud.view.index'), $dataView);
        }

        return $this->getData($request);
    }

    /**
     * data
     *
     * @param Request $request
     * @return void
     */
    public function data(Request $request)
    {
        return $this->getData($request);
    }

    /**
     * Create
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        return $this->edit($request, Crypt::encrypt(0));
    }

    /**
     * Edit
     *
     * @param Request $request
     * @param [type] $rowId
     * @return void
     */
    public function edit(Request $request, $rowId)
    {
        try {
            if (!is_numeric($rowId)) {
                $rowId = Crypt::decrypt($rowId);
            }
        } catch (Exception $e) {
            if ($this->vue == true) {
                abort(404, 'Error al encontrar el registro.');
                // return AppController::responseJson(false, ['message' => 'Error al encontrar el registro.']);
            }

            $request->session()->flash('alert-msj', 'Error al encontrar el registro.');
            $request->session()->flash('alert-type', 'danger');

            return redirect()->back();
        }

        $model  = $this->model;
        $pk     = $model->getKeyName();
        $select = $this->getSelect();
        $model  = $model->select($select);

        foreach ($this->listJoin as $j) {
            $model->{$j['type']}($j['t'] . ' AS ' . $j['as'], $j['pk'], $j['fk']);
        }

        // $model->where($pk, $rowId);
        $data          = $model->find($rowId);
        $stringRequest = empty($request->all()) ? '' : '?' . http_build_query($request->all());

        if ($data) {
            $data->id = !$this->vue ? encrypt($data->{$pk}) : $data->{$pk};
            $route    = route($this->module . '.update', Crypt::encrypt($rowId), ) . $stringRequest;
        } else {
            $route = route($this->module . '.store') . $stringRequest;
        }

        $fields = collect($this->fields)->filter(function ($item) {
            return $item['editable'];
        })->values();

        $dataView = [
            'pageTitle'  => $this->title . ($rowId == 0 ? ' | <span style="font-size:12px;">Nuevo</span>' : ' | <span style="font-size:12px;">Editar</span>'),
            'data'       => $data,
            'fields'     => $fields,
            'route'      => $route,
            'routeIndex' => route($this->module . '.index', ['id' => $request->id]),
        ];

        if ($this->vue == true) {
            return AppController::responseJson(['data' => $dataView]);
        }

        return view(config('hcode.crud.view.edit'), $dataView);
    }

    /**
     * Store
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        return $this->update($request, Crypt::encrypt(0));
    }

    /**
     * Update
     *
     * @param Request $request
     * @param [type] $rowId
     * @return void
     */
    public function update(Request $request, $rowId)
    {
        try {
            if (!is_numeric($rowId)) {
                $rowId = Crypt::decrypt($rowId);
            }
        } catch (Exception $e) {
            if ($this->vue == true) {
                abort(406, 'Error al almacenar el registro.');
                // return AppController::responseJson(false, ['message' => 'Error al almacenar el registro.']);
            }
            $request->session()->flash('alert-msj', 'Error al almacenar el registro.');
            $request->session()->flash('alert-type', 'danger');

            return redirect()->back();
        }

        $requestData = $request->all();
        $fields      = collect($this->fields)->filter(function ($item) {
            return $item['editable'];
        });
        $fieldsValidate = [];
        //$niceNameAttr   = [];

        foreach ($requestData as $key => $value) {
            $field = $fields->first(function ($item) use ($key) {
                return $item['fieldAs'] == $key;
            });
            if ($field && $field['validate']) {
                $fieldsValidate[$field['fieldAs']] = $field['validate'];
            }
            if ($field && $field['type'] == 'password' && ($rowId == 0 || $value)) {
                //$fieldsValidate[$field['fieldAs']] = 'required';
                $fieldsValidate['passwordconfirm'] = 'required|same:password';
            }
        }

        if (!empty($fieldsValidate)) {
            $validator = Validator::make($request->all(), $fieldsValidate);
            //$validator->setAttributeNames($niceNameAttr);
            if ($validator->fails()) {
                if ($this->vue == true) {
                    return response()->json(['message' => 'Error en validación de campos', 'errors' => $validator->errors()], 422);

                    // return AppController::responseJson(['message' => 'Campos requeridos', 'errors' => $validator->errors()], 422);
                }

                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        if ($rowId == 0) {
            if ($this->enabledFirstOr) {
                $arr = [];
                foreach ($this->fiedsFirstOr as $value) {
                    if (array_key_exists($value, $requestData)) {
                        $arr[$value] = $requestData[$value];
                    } else {
                        $item = collect($this->listHidden)->first(function ($item) use ($value) {return $item['field'] == $value;});
                        if ($item) {
                            $arr[$value] = $item['value'];
                        }
                    }
                }
                $model = $this->model->firstOrNew($arr);
                if ($model->deleted_at) {
                    $model->deleted_at = null;
                }
            } else {
                $model = $this->model;
            }
        } else {
            $model = $this->model->find($rowId);
        }

        try {
            $excluede = ['bool', 'password', 'date', 'datetime', 'time'];
            $dates    = ['date', 'datetime', 'time'];
            foreach ($fields as $key => $value) {
                if (isset($value['fieldAs'])) {
                    if ($value['type'] == 'bool') {
                        $model->{$value['fieldAs']} = isset($requestData[$value['fieldAs']]) ? $requestData[$value['fieldAs']] : false;
                    }
                    if ($value['type'] == 'password' && $requestData[$value['fieldAs']]) {
                        $model->{$value['fieldAs']} = Hash::make($requestData[$value['fieldAs']]);
                    }
                    if ($value['type'] == 'numeric') {
                        $model->{$value['fieldAs']} = $requestData[$value['fieldAs']];
                    }
                    if (!in_array($value['type'], $excluede)) {
                        $model->{$value['fieldAs']} = !empty($requestData[$value['fieldAs']]) ? $requestData[$value['fieldAs']] : null;
                    }
                    if (in_array($value['type'], $dates)) {
                        $valueField = str_replace('/', '-', $requestData[$value['fieldAs']]);

                        $dateOrTime = null;
                        if ($valueField) {
                            $dateOrTime = new Carbon($valueField);
                        }
                        $model->{$value['fieldAs']} = $dateOrTime;
                    }
                    if ($value['type'] == 'file') {
                        if ($request->hasFile($value['field'])) {
                            $fileName = uniqid() . date('YmdHis') . '.' . $request->file($value['field'])->getClientOriginalExtension();
                            $request->file($value['field'])->storeAs($value['path'], $fileName, 'public');
                            $model->{$value['field']} = $fileName;
                        }
                    }
                    // else {
                    //     $model->{$value['fieldAs']} = $requestData[$value['fieldAs']];
                    // }
                }
            }

            foreach ($this->listHidden as $arr) {
                $model->{$arr['field']} = $arr['value'];
            }

            $model->save();
        } catch (QueryException $e) {
            if ($this->vue == true) {
                abort(406, 'Error al almacenar el registro.' . $e->getMessage());

                // return response()->json(['message' => 'Error al almacenar el registro.']);
            }

            $request->session()->flash('alert-msj', 'Error al almacenar el registro.' . (env('APP_DEBUG') ? ('<br>Code: ' . $e->getCode() . ' | Message: ' . $e->errorInfo[2]) : ''));
            $request->session()->flash('alert-type', 'danger');

            return redirect()->back();
        }

        if ($this->vue == true) {
            return response()->json(['message' => 'El registro se ha almacenado correctamente.']);

            // return AppController::responseJson(['message' => 'El registro se ha almacenado correctamente.']);
        }

        $request->session()->flash('alert-msj', 'El registro se ha almacenado correctamente.');
        $request->session()->flash('alert-type', 'success');

        return redirect()->route($this->module . '.index', ['id' => $request->id]);
    }

    /**
     * Destroy
     *
     * @param Request $request
     * @param [type] $rowId
     * @return void
     */
    public function destroy(Request $request, $rowId)
    {
        try {
            if (!is_numeric($rowId)) {
                $rowId = Crypt::decrypt($rowId);
            }
        } catch (Exception $e) {
            if ($this->vue == true) {
                abort(406, 'Error al eliminar el registro.');

                // return AppController::responseJson(true, ['message' => 'Error al eliminar el registro.']);
            }

            $request->session()->flash('alert-msj', 'Error al eliminar el registro.');
            $request->session()->flash('alert-type', 'danger');

            return redirect()->back();
        }

        try {
            $pk = $this->model->getKeyName();

            if ($this->destroy) {
                $this->model->where($pk, $rowId)->delete();
            } else {
                $this->model->where($pk, $rowId)->update(['deleted_at' => date('Y-m-d H:i:s')]);
            }
        } catch (QueryException $e) {
            if ($this->vue == true) {
                abort(406, 'Error al eliminar el registro.');

                // return AppController::responseJson(true, ['message' => 'Error al eliminar el registro.']);
            }
            $request->session()->flash('alert-msj', 'Error al eliminar el registro.' . (env('APP_DEBUG') ? ('<br>Code: ' . $e->getCode() . ' | Message: ' . $e->errorInfo[2]) : ''));
            $request->session()->flash('alert-type', 'danger');

            return redirect()->back();
        }

        if ($this->vue == true) {
            return AppController::responseJson(['message' => 'Registro eliminado correctamente.']);
        }

        $request->session()->flash('alert-msj', 'Registro eliminado correctamente.');
        $request->session()->flash('alert-type', 'warning');

        return redirect()->back();
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function setVue()
    {
        $this->vue = true;
    }

    /**
     * setTitle
     *
     * @param [type] $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * setModel
     *
     * @param [type] $model
     * @return void
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * setComponent
     *
     * @param [type] $model
     * @return void
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }

    /**
     * setJoin
     *
     * @param [type] $table
     * @param [type] $as
     * @param [type] $pk
     * @param [type] $fk
     * @param string $type
     * @return void
     */
    public function setJoin($table = null, $as = null, $pk = null, $fk = null, $type = 'leftJoin')
    {
        if (!$table || !$as || !$pk || !$fk) {
            dd('Parametros incompletos para la setLeftJoin, $this->setLeftJoin("nombre_tabla", "alias", "primaryKey", "foreignKey")');
        }

        $types = ['leftJoin', 'rightJoin', 'join'];
        if (!in_array($type, $types)) {
            dd('Tipo de join incorrecto! solamente se permite: ' . implode(', ', $types));
        }

        $this->listJoin[] = [
            'type' => $type,
            't'    => $table,
            'as'   => $as,
            'pk'   => $pk,
            'fk'   => $fk,
        ];
    }

    /**
     * setHidden
     *
     * @param [type] $hidden
     * @return void
     */
    public function setHidden($hidden = null)
    {
        if (!$hidden || !is_array($hidden)) {
            dd('Parametro de tipo Array requerido para setHidden');
        }

        $keys = ['field', 'value'];

        foreach ($keys as $value) {
            if (!isset($hidden[$value])) {
                dd('El index (' . $value . ') y el valor del index es requerido para setHidden, $this->setHidden(["campo" => "nombre_campo", "valor" => "valor_campo"])');
            }
        }

        $this->listHidden[] = $hidden;
    }

    /**
     * setExports
     *
     * @param [type] $params
     * @return void
     */
    public function setExports($params)
    {
        if (is_array(($params))) {
            foreach ($params as $p) {
                $this->exports[] = $p;
            }
        } else {
            $this->exports[] = $params;
        }
    }

    /**
     * setField
     *
     * @param [type] $aParams
     * @return void
     */
    public function setField($aParams)
    {
        $aParams      = Collect($aParams);
        $types        = Collect(['string', 'multi', 'numeric', 'date', 'datetime', 'time', 'bool', 'listbool', 'combobox', 'password', 'enum', 'file', 'textarea', 'url', 'summernote']);
        $keysOfParams = Collect(['field', 'name', 'editable', 'show', 'type', 'class', 'default', 'decimals', 'collect', 'validate', 'gridfill', 'path', 'passwordconfirm']);

        $aParams->each(function ($item, $index) use ($keysOfParams) {
            $exist = $keysOfParams->first(function ($key) use ($index) {
                return $key == $index;
            });
            if (!$exist) {
                dd('Llave (' . $index . ') no permitido! solamente se permiten: ' . $keysOfParams->implode(','));
            }
        });

        if (!$aParams->has('field')) {
            dd('Index (campo) es requerido');
        }

        $valueCampo = $aParams->first(function ($item, $index) {
            return $index == 'field';
        });

        if (!$valueCampo) {
            dd('Valor requerido para index (campo)');
        }

        $reCampo  = str_replace([' as ', ' AS '], '.', $valueCampo);
        $expCampo = explode('.', $reCampo);

        $defaults = Collect([
            'field'           => $valueCampo,
            'fieldAs'         => end($expCampo),
            'name'            => str_replace('_', ' ', ucfirst($valueCampo)),
            'editable'        => true,
            'show'            => true,
            'type'            => 'string',
            'class'           => null,
            'default'         => null,
            'decimals'        => 0,
            'collect'         => null,
            'validate'        => null,
            'gridfill'        => false,
            'path'            => null,
            'passwordconfirm' => true,
        ]);

        $arr = $defaults->mapWithKeys(function ($item, $index) use ($aParams) {
            $value = $aParams->first(function ($i, $key) use ($index) {
                return $key == $index;
            });
            if ($value === null) {
                $value = $item;
            }

            return [$index => $value];
        });

        $type = $types->first(function ($item) use ($arr) {
            return $item == $arr['type'];
        });

        if (!$type) {
            dd('Tipo (' . $arr['tipo'] . ') invalido! solamente se permiten: ' . $tipos->implode(','));
        }
        if ($type == 'file' && !$arr['path']) {
            dd('Para el tipo (file) el index (path) es requerido');
        }
        if ($type == 'combobox' && !$arr['collect']) {
            dd('Para el tipo (combobox) el index (collect) es requerido');
        }
        if ($type == 'listbool' && $arr['collect']) {
            dd('Para el tipo (listbool) el index (collect) es requerido');
        }
        if ($type == 'combobox') {
            $arr['show'] = false;
        }

        $this->fields[] = $arr;
    }

    /**
     * setWhere
     *
     * @param [type] $field
     * @param string $condition
     * @param string $value
     * @return void
     */
    public function setWhere($field = null, $condition = '=', $value = 'NULL')
    {

        $arrConditions = ['=', '>', '<', '<>', '>=', '<=', '!='];
        if ($value == 'NULL') {
            $value     = $condition;
            $condition = '=';
        } elseif (!in_array($condition, $arrConditions)) {
            dd('La condicion es invalida para setWhere, solo se permite:' . implode(', ', $arrConditions));
        }

        $this->listWhere[] = ['field' => $field, 'condition' => $condition, 'value' => $value];
    }

    /**
     * setWhereIn
     *
     * @param [type] $field
     * @param [type] $arr
     * @return void
     */
    public function setWhereIn($field, $arr)
    {
        $this->listWhereIn[] = ['field' => $field, 'arr' => $arr];
    }

    /**
     * setOrderBy
     *
     * @param array $orderBy
     * @param string $direction
     * @return void
     */
    public function setOrderBy($orderBy, $direction = 'asc')
    {
        $types = ['asc', 'desc'];
        if (!in_array($direction, $types)) {
            dd('Tipo de orden incorrecto! solamente se permite: asc, desc');
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $key => $value) {
                $this->listOrderBy[] = [
                    'field'     => $value,
                    'direction' => $direction,
                ];
            }
        } else {
            $this->listOrderBy[] = [
                'field'     => $orderBy,
                'direction' => $direction,
            ];
        }
    }

    /**
     * setButtonAction
     *
     * @param  mixed $params
     *
     * @return void
     */
    public function setButtonAction($aParams)
    {
        $allowed = ['title', 'routeName', 'class', 'icon'];
        $this->allowed($aParams, $allowed);
        $this->buttonActions[] = $aParams;
    }

    /**
     * allowed
     *
     * @param  mixed $params
     * @param  mixed $allowed
     *
     * @return void
     */
    private function allowed($aParams, $allowed)
    {
        foreach ($aParams as $index => $val) {
            if (!in_array($index, $allowed)) {
                dd($index . ' no es un parametro permitido para setButtonAction, solo se permiten:' . implode(', ', $allowed));
            }
        }
    }

    /**
     * setPermissionsExtra
     *
     * @param array $permissionsExtra
     * @return void
     */
    public function setPermissionsExtra($permissionsExtra)
    {
        if (is_array($permissionsExtra)) {
            $this->permissionsExtra = $permissionsExtra;
        } else {
            dd('Parametro de type Array requerido para setPermissionsExtra');
        }
    }

    /**
     * setPermissions
     *
     * @param string $module
     * @return void
     */
    public function setPermissions($module = '')
    {
        $this->module     = $module;
        $permissionsExtra = $this->permissionsExtra;
        $this->middleware(function ($request, $next) use ($module, $permissionsExtra) {
            $permissions       = array_merge(['create', 'edit', 'destroy'], $permissionsExtra);
            $this->permissions = EssenAccessController::modulePermissions($module, $permissions);

            return $next($request);
        });
    }

    /**
     * setDestroy
     *
     * @return void
     */
    public function setDestroy()
    {
        $this->destroy = true;
    }

    /**
     * setEnabledFirstOrNew
     *
     * @param [type] $params
     * @return void
     */
    public function setEnabledFirstOrNew($params)
    {
        if (!is_array($params)) {
            dd('Parametro de tipo Array requerido para setEnabledFirstOr.');
        }
        $this->enabledFirstOr = true;
        $this->fiedsFirstOr   = $params;
    }

    /**
     * setOptions
     *
     * @param [type] $params
     * @return void
     */
    public function setOptions($params)
    {
        if (!is_array($params)) {
            dd('Parametro de tipo Array requerido para setOptions.');
        }
        $this->options = $params;
    }

    /**
     * getModule
     *
     * @return void
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * getPermissions
     *
     * @return void
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * getColumns
     *
     * @param boolean $onlyShow
     * @return void
     */
    public function getColumns($onlyShow = false)
    {
        $fields = $this->fields;
        if (is_array($this->fields)) {
            $fields = Collect($this->fields);
        }
        if ($onlyShow) {
            $columns = $fields->where('show', true)->map(function ($item) {
                return ['field' => $item['field'], 'fieldAs' => $item['fieldAs'], 'name' => $item['name'], 'type' => $item['type'], 'class' => $item['class'], 'decimals' => $item['decimals'], 'path' => $item['path']];
            });
        } else {
            $columns = $fields->map(function ($item) {
                return ['field' => $item['field'], 'fieldAs' => $item['fieldAs'], 'name' => $item['name'], 'type' => $item['type'], 'class' => $item['class'], 'decimals' => $item['decimals'], 'path' => $item['path']];
            });
        }

        return $columns->values();
    }

    /**
     * getFields
     *
     * @return void
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * getSelect
     *
     * @return void
     */
    public function getSelect($onlyShow = false)
    {
        $columns    = $this->getColumns($onlyShow);
        $primaryKey = $this->model->getKeyName();
        $table      = $this->model->getTable();
        $select     = [];

        // if ($primaryKey != 'id') {
        $select[] = $table . '.' . $primaryKey . ' AS __id';
        // } else {
        //     $select[] = $primaryKey;
        // }

        foreach ($columns as $c) {
            if (!in_array($c['field'], $select)) {
                $select[] = $c['field'];
            }
        }

        return $select;
    }

    /**
     * getActions
     *
     * @param [type] $path
     * @param [type] $rowId
     * @return void
     */
    private function getActions($request, $path, $rowId)
    {
        $html = '<div class="text-center">';

        if (count($this->buttonActions) > 1) {
            $html .= '<div class="btn-group btn-group-xs">
                <a class="btn btn-sm btn-info2 btnMoreActions" title="Más acciones" href="javascript:void(0)">
                    <span class="fas fa-ellipsis-h"></span>
                </a>
            </div> ';
        } else {
            foreach ($this->buttonActions as $item) {
                $html .= '<div class="btn-group btn-group-xs">
                    <a class="btn btn-sm ' . (isset($item['class']) ? $item['class'] : 'btn-default') . '" title="' . $item['title'] . '" href="' . route($item['routeName'], $rowId) . '">
                        <span class="' . (isset($item['icon']) ? $item['icon'] : 'fas fa-question') . '"></span>
                    </a>
                </div> ';
            }
        }

        if ($this->permissions['edit']) {
            $html .= '<div class="btn-group btn-group-xs">
                <a class="btn btn-sm btn-primary" title="Editar" href="' . route(str_replace('/', '.', $path) . '.edit', $rowId) . $request . '">
                    <span class="fas fa-pencil-alt"></span>
                </a>
            </div> ';
        }
        if ($this->permissions['destroy']) {
            $html .= '<div class="btn-group btn-group-xs">
                <button class="btn btn-sm btn-danger" title="Eliminar" onclick="$(\'#modal-destroy\').modal(\'show\');$(\'#frm-destroy\').attr(\'action\', \'' . route(str_replace('/', '.', $path) . '.destroy', $rowId) . $request . '\')">
                    <span class="fa fa-trash"></span>
                </button>
            </div> ';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * getQuery
     *
     * @param Request $request
     * @return void
     */
    public function getQuery(Request $request)
    {
        $query = $this->getData($request, true);
        dd($query);
    }

    /**
     * getListJoin
     *
     * @return void
     */
    public function getListJoin()
    {
        return $this->listJoin;
    }

    /**
     * getData
     *
     * @param Request $request
     * @param boolean $toSql
     * @return void
     */
    public function getData(Request $request, $toSql = false)
    {
        $model   = $this->model;
        $select  = $this->getSelect(true);
        $columns = $this->getColumns(true);
        $query   = $model::select($select);

        foreach ($this->listJoin as $j) {
            $query->{$j['type']}($j['t'] . ' AS ' . $j['as'], $j['pk'], '=', $j['fk']);
        }

        foreach ($this->listWhere as $arr) {
            $query->where($arr['field'], $arr['condition'], $arr['value']);
        }

        foreach ($this->listWhereIn as $arr) {
            $query->whereIn($arr['field'], $arr['arr']);
        }

        if ($request->order) {
            if ($this->vue) {
                if ($request->order) {
                    $order = is_array($request->order) ? (Object) $request->order : $request->order;
                    if ($order->column) {
                        $query->orderBy($order->column, $order->dir);
                    }
                }
            } else {
                foreach ($request->input('order', []) as $arr) {
                    $field = explode(' ', $columns[$arr['column']]['field']);
                    $query->orderBy(array_shift($field), $arr['dir']);
                }
            }
        } else {
            foreach ($this->listOrderBy as $arr) {
                $query->orderBy($arr['field'], $arr['direction']);
            }
        }

        $searchBy = $request->search;
        if (is_array($request->search)) {
            $searchBy = isset($request->search['value']) ? $request->search['value'] : null;
        }

        if ($searchBy) {
            $columnsFilter = $columns->filter(function ($item) {
                return in_array($item['type'], ['string', 'numeric', 'bool']);
            });

            $count     = 0;
            $whereLike = '';

            foreach ($columnsFilter as $cf) {
                $field = explode(' ', $cf['field']);

                if ($cf['type'] == 'string' || $cf['type'] == 'numeric') {
                    $whereLike .= $count == 0 ? '(' : ' or ';
                    $whereLike .= array_shift($field) . " like '%" . $searchBy . "%'";
                } elseif ($cf['type'] == 'bool') {
                    $whereLike .= $count == 0 ? '(' : ' or ';
                    $whereLike .= "(case when 'activo' like '%" . $searchBy . "%' then {$cf['field']} = true when 'inactivo' like '%" . $searchBy . "%' then {$cf['field']} = false end)";
                }
                $count++;
            }
            $whereLike .= ')';
            $query->whereRaw($whereLike);
        }

        $recordsTotal = $query->count();

        $query->skip($request->start)->take($request->length);

        if ($toSql) {
            return $query->toSql();
        }

        $data            = $query->get();
        $recordsFiltered = $data->count();
        $path            = $request->path();
        $formatDateTypes = ['date' => 'd/m/Y', 'datetime' => 'd/m/Y H:i', 'time' => 'H:i'];

        $excludeRequestToAction = ['draw', 'columns', 'order', 'start', 'length', 'search', 'responseJson', '_'];
        $requestToActions       = collect($request->all())->filter(function ($item, $key) use ($excludeRequestToAction) {return !in_array($key, $excludeRequestToAction) ? $item : null;})->toArray();
        $requestToActions = empty($requestToActions) ? '' : '?' . http_build_query($requestToActions);

        $arr = $data->map(function ($d, $index) use ($columns, $formatDateTypes, $path, $requestToActions) {
            $dates  = $d->getDates();
            $data   = $d->toArray();
            $values = [];
            $i      = 0;

            foreach ($data as $key => $value) {
                if ($key !== '__id') {
                    if (isset($columns[$i])) {
                        if (in_array($key, $dates)) {
                            $date = $columns[$i];
                            if ($value) {
                                $value = (new Carbon($value))->setTimezone('CST')->format($formatDateTypes[$date['type']]);
                            }
                        }
                        if ($columns[$i]['type'] == 'file') {
                            $pathFile = Storage::url($columns[$i]['path'] . '/' . $value);
                            $value    = ($value ? '<div class="text-center"><a href="' . $pathFile . '" target="_blank"><i class="fas fa-cloud-download-alt text-info"></i></a></div>' : '');
                        }

                        if ($columns[$i]['type'] == 'bool') {
                            $value = ($value ? '<div class="text-center"><i class="fas fa-check text-center"></i></div>' : '');
                        }

                        if ($this->vue == true) {
                            $values[$key] = $value;
                        } else {
                            $values[] = $value;
                        }
                    }
                    $i++;
                } else {
                    if ($this->vue == true) {
                        $values['__id'] = $value;
                    }
                }

                // if ($this->vue == true) {
                //     $values[$key] = $value;
                // } else {
                //     $values[] = $value;
                // }
            }
            if ($this->vue != true) {
                $path     = str_replace(['/data', 'api/'], ['', ''], $path);
                $values[] = $this->getActions($requestToActions, $path, Crypt::encrypt($data['__id'] ?? 0));
            }

            return $values;
        });

        return response()->json([
            'draw'            => $request->draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $arr,
        ]);
    }
}
