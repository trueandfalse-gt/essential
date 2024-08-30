<?php
namespace Trueandfalse\Essential\Middleware;

use Closure;
use Trueandfalse\Essential\Models\Tenant\Tenant;

class AppTenant
{

    public function handle($request, Closure $next)
    {
        $domain = $request->server()['HTTP_HOST'];

        $tenant = Tenant::where('domain', $domain)->where('active', 1)->first();

        if (!$tenant) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Dominio inexistente.' . $domain], 404);
            } else {
                return response()->view('errors::404', ['message' => 'Dominio inexistente.']);
            }
        }

        config([
            'database.connections.mysql.tenantid' => $tenant->tenant_id,
            'database.connections.mysql.host'     => $tenant->db_host,
            'database.connections.mysql.database' => $tenant->db,
            'database.connections.mysql.username' => $tenant->db_username,
            'database.connections.mysql.password' => $tenant->db_password,
        ]);

        return $next($request);
    }
}
