<?php

namespace App\Http\Middleware;

use App\Models\ActivityLogs;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLogActivity
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
        if(Auth::check()) {
            $log = new ActivityLogs;
            $log->url = $request->fullUrl();
            $log->user_action = "Visita il link";
            $log->user_id = Auth::id();
            $log->save();
        }
        return $next($request);
    }
}
