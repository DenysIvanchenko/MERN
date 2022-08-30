<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        $route = Route::getRoutes()->match($request);
        if (! $request->expectsJson()) {
            if(str_contains($route->uri, 'admin')){
                return route('login-page');
            }
            else if(str_contains($route->uri, 'user')){
                return route('login-page');                
            }else{
                return route('login-page');
            }
        }
    }
}
