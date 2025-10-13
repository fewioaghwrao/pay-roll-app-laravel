<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminAccountController extends Controller
{
    public function destroySelf(Request $request)
    {
        $guard = 'admin';
        $admin = Auth::guard($guard)->user();

        // もし最終1人の管理者なら安全のためブロック（任意）
        $adminModel = get_class($admin);
        $adminsLeft = $adminModel::query()->count();
        if ($adminsLeft <= 1) {
            return back()->withErrors(['最終管理者は削除できません。別管理者を作成してから再実行してください。']);
        }

        DB::transaction(function () use ($admin) {
            $admin->delete(); // ソフトデリート/物理削除はモデル設定に依存
        });

        Auth::guard($guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', '管理者登録を解除し、ログアウトしました。');
    }
}
