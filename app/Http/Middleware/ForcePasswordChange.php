<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password) {
            $exemptRoutes = ['password.change', 'password.update', 'logout'];

            if (! in_array($request->route()?->getName(), $exemptRoutes)) {
                return redirect()->route('password.change');
            }
        }

        return $next($request);
    }
}
