<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class IsStudent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->user()->user_type=='student'){
            return $next($request);
        }
        else
        {
            Auth::logout();
            request()->session()->flash('error','You do not have any permission to access this page');
            return redirect()->route('login-page');
        }
    }
}
