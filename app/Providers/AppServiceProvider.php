<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route; // ★ 追加
use App\Http\Middleware\UserAuth;     // ★ 追加
use App\Http\Middleware\AdminAuth;    // ★ 追加

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ★ Kernel に加えて、ルータ側にもエイリアスを明示登録（保険）
        Route::aliasMiddleware('user.auth', UserAuth::class);
        Route::aliasMiddleware('admin.auth', AdminAuth::class);
    }
}
