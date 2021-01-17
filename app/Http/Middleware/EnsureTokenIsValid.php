<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\ResponseAPI;
use Illuminate\Support\Facades\Auth;


class EnsureTokenIsValid
{
    use ResponseAPI;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::guard('api')->check())
        {
            return $next($request);      
        } 
        return $this->error('token is invalid or expired!', '401');   
          
    }
}
