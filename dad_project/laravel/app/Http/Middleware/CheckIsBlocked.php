<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckIsBlocked
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
        if(Auth::user()->isBlocked()){
            return response()->json(['error'=>'You have been blocked from this website. 
                                    For more info visit your e-mail'], 401);
        }
        return $next($request);
    }
}
