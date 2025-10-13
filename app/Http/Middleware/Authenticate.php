<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        if (! $request->expectsJson()) {
            // 管理者ルートの判定はパスだけでなくルート名でも判定する（テスト環境での一致精度向上）
            $route = $request->route();
            $routeName = is_object($route) && method_exists($route, 'getName') ? $route->getName() : null;

            $path = ltrim($request->path(), '/');

            if (str_starts_with((string)$routeName, 'admin.') || $request->is('admin') || $request->is('admin/*') || str_starts_with($path, 'admin')) {
                return route('admin.login.form');
            }

            // それ以外は一般ユーザーログインへ
            return route('login');
        }

        return null;
    }
}