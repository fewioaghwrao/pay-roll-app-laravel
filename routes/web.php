<?php

use Illuminate\Support\Facades\Route;

// ===== User =====
use App\Http\Controllers\User\HomeController as UserHome;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PayslipAiController;

// ===== Admin =====
use App\Http\Controllers\Admin\HomeController as AdminHome;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminRegisterController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CsvImportController; //CSVインポート
use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\LogsController;
use App\Http\Controllers\Admin\UserAiController;

/*
|--------------------------------------------------------------------------
| 管理者：ログイン処理
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',  [AdminAuthController::class, 'showLogin'])->name('login.form');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

Route::delete('/admin/unregister', [AdminAccountController::class, 'destroySelf'])
    ->middleware('auth:admin')
    ->name('admin.unregister');

/*
|--------------------------------------------------------------------------
| 管理者：保護ルート
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('/', [AdminHome::class, 'index'])->name('home');

    Route::get('/users/{user}', [AdminHome::class, 'showUser'])->name('user.show');
    Route::post('/users/{user}/payslips', [AdminHome::class, 'createPayslip'])->name('payslip.store');
    Route::post('/users/{user}/withholding', [AdminHome::class, 'createWithholding'])->name('withholding.store');
    Route::delete('/users/{user}', [AdminHome::class, 'destroyUser'])->name('user.destroy');

    Route::get('/register', [AdminRegisterController::class, 'create'])->name('register.create');
    Route::post('/register', [AdminRegisterController::class, 'store'])->name('register.store');

    Route::get('/employees/create', [AdminUserController::class, 'create'])->name('employee.create');
    Route::post('/employees', [AdminUserController::class, 'store'])->name('employee.store');

    Route::get('/csv-import', [CsvImportController::class, 'create'])->name('csv.create');
    Route::post('/csv-import', [CsvImportController::class, 'store'])->name('csv.store');
    Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');

    Route::post('/users/{user}/ai-explain', [UserAiController::class, 'explain'])
        ->name('user.ai.explain')
        ->middleware('throttle:6,1'); // 1分6回
});

if (app()->environment('local')) {
    Route::get('/admin/whoami', function () {
        return response()->json([
            'admin_guard' => auth('admin')->check(),
            'web_guard'   => auth('web')->check(),
            'path'        => request()->path(),
        ]);
    });
}

/*
|--------------------------------------------------------------------------
| 一般ユーザー：ログイン関連
|--------------------------------------------------------------------------
*/
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.do');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| 一般ユーザー：保護ルート (auth:web)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web'])->group(function () {
    Route::get('/', [UserHome::class, 'index'])->name('user.home');
    Route::get('/payslips/{payslip}', [UserHome::class, 'showPayslip'])->name('payslip.show');
    Route::get('/payslips/{payslip}/pdf', [UserHome::class, 'downloadPayslipPdf'])->name('payslip.pdf');
    Route::get('/withholding/pdf', [UserHome::class, 'downloadWithholdingPdf'])->name('withholding.pdf');

    Route::post('/payslips/{payslip}/ai-explain', [PayslipAiController::class, 'explain'])
        ->name('payslip.ai.explain')
        ->middleware('throttle:6,1'); // 1分に6回まで
});

/*
|--------------------------------------------------------------------------
| 補助：/home → /
|--------------------------------------------------------------------------
*/
Route::get('/home', fn () => redirect()->route('user.home'))->name('home');

