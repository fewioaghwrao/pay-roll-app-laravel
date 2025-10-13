<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * すべてのHTTPリクエストに適用されるグローバルミドルウェア
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * ミドルウェアグループ定義（web / api）
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class, // 使うなら有効化
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * ルート個別指定用のミドルウェアエイリアス
     * Laravel 12 は $routeMiddleware ではなく $middlewareAliases
     */
    protected $middlewareAliases = [
        // Laravel組み込み
        'auth'       => \App\Http\Middleware\Authenticate::class,
        'verified'   => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // あなたのカスタム
        // 'admin.auth' => \App\Http\Middleware\AdminAuth::class,
       // 'user.auth'  => \App\Http\Middleware\UserAuth::class,
    ];
}

