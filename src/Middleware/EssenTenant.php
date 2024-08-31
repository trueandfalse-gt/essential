<?php
namespace Trueandfalse\Essential\Middleware;

use Closure;
use Trueandfalse\Essential\Models\Tenant\Tenant;

class EssenTenant
{

    public function handle($request, Closure $next)
    {
        $domain = $request->server()['HTTP_HOST'];

        $tenant = Tenant::where('domain', $domain)->where('active', 1)->first();

        if (!$tenant) {
            $message = 'Dominio no permitido';
            if ($request->expectsJson()) {
                return response()->json(['error' => $message, $domain], 404);
            } else {
                return response()->view('errors::404', ['message' => $message]);
            }
        }

        config([
            'database.connections.mysql.tenantid' => $tenant->id,
            'database.connections.mysql.host'     => $tenant->database_host,
            'database.connections.mysql.database' => $tenant->database_name,
            'database.connections.mysql.username' => $tenant->database_user,
            'database.connections.mysql.password' => $tenant->database_password,
        ]);

        return $next($request);
    }
}
