<?php

namespace App\Http\Middleware;

use Closure;
use Log;
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

        if ($request->has('trigger_word') && $request->has('text')) {
            Log::error("Im here");
            $triggerWord = $request->get('trigger_word');
            Log::warn($triggerWord);
            $text = trim(str_replace($triggerWord,"",$request->get("text")));
            Log::warn($text);
            $request->merge(array('text' => $text));
        }
        Log::warn("didnt get in");
        return $next($request);
    }
}
