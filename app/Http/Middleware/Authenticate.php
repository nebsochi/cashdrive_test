<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use App\Traits\ResponseAPI;

class Authenticate extends Middleware
{
    use ResponseAPI;
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    // protected function redirectTo($request)
    // {
    //     if(!auth()->check())
    //     {
    //         return $this->error('token is invalid or expired!', '401');       
    //     } 
       
    //     return $next($request);
    // }
}
