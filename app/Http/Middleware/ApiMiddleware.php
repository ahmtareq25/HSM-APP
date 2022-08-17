<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isJson() and $request->expectsJson()) {
            return $next($request);
        }

        return appResponse()->failed(config('status_code.validation_fail'), __('Failed'), __('Expect JSON request only.'));
    }
}
