<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserAuth
{
    public function handle(Request $request, Closure $next)
    {
        // ★ 管理者配下はこのミドルウェアの対象外
        if ($request->is('admin*')) {
            return $next($request);
        }

        if (! session()->has('user_id')) {
            return redirect()->route('login'); // ← 'login.form' ではなく 'login'
        }
        return $next($request);
    }
}

