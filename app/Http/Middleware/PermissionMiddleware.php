<?php

namespace App\Http\Middleware;

use App\Services\PermissionManagement;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()
            && (new PermissionManagement())->checkPermissionByAuth(auth()->user(), Route::currentRouteName())) {
            return $next($request);
        }

        return appResponse()->failed(config('status_code.permission_error'), __('Permission Denied'));

    }
}
