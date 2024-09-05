<?php
namespace Trueandfalse\Essential\Middleware;

use Auth;
use Closure;
use Trueandfalse\Essential\Http\Controllers\EssenAccessController;

class EssenAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->id()) {
            abort(404, 'unauthenticated');
        }

        $route = $request->route()->getName();
        EssenAccessController::accessRoute($request, $route);

        return $next($request);
    }
}
