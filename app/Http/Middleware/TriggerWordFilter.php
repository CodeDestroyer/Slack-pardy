<?php

namespace App\Http\Middleware;

use Closure;

class TriggerWordFilter
{
    /**
     * Handle an incoming request.
     * Strip the Trigger word out of the text
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
