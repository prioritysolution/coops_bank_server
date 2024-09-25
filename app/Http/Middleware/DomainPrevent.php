<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class DomainPrevent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
     // Check if the request originated from the allowed domain
     if ($request->getHttpHost() !== '116.193.129.229:8080') {
        return response($request->getHttpHost(), 401);
    }

        
        return $next($request); 
    }
}