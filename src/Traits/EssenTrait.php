<?php
namespace Trueandfalse\Essential\Traits;

use Illuminate\Http\Request;

trait EssenTrait
{
    public function columnsConvert($colums)
    {
        return $colums->map(function ($column) {
            return [
                'key'      => $column['field'],
                'name'     => $column['name'],
                'type'     => $column['type'],
                'class'    => $column['class'],
                'decimals' => $column['decimals'],
            ];
        });
    }

    public function moduleBase($routeName, $separator = '.')
    {
        $arr = explode('.', $routeName);
        array_pop($arr);

        return implode($separator, $arr);
    }

    public static function alertSuccess(Request $request, $message = null)
    {
        $request->session()->flash('alert-message', !$message ? 'El registro sea guardo correctamente.' : $message);
        $request->session()->flash('alert-type', 'success');
    }

    public static function alertError(Request $request, $exception = null, $message = null)
    {
        if ($message) {
            $message = $message;
        } else {
            $message = 'Error al guardar el registro.';
        }
        if ($exception) {
            $message .= (env('APP_DEBUG') ? ('<br>Code: ' . $exception->getCode() . ' | Message: ' . $exception->errorInfo[2]) : '');
        }

        $request->session()->flash('alert-message', $message);
        $request->session()->flash('alert-type', 'danger');
    }

}
