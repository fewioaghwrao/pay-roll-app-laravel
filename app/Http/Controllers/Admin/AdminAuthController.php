<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        // ビューは resources/views/admin/login.blade.php などに用意
        return view('admin.auth.login'); // ← 階層を auth 付きに
    }

    public function login(Request $request)
    {

    $credentials = $request->validate([
        'email'    => ['required','email'],
        'password' => ['required'],
    ]);

    // 念のため admin ガードを明示
    \Illuminate\Support\Facades\Auth::shouldUse('admin');

    if (\Illuminate\Support\Facades\Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(route('admin.home')); // ← ここは実在する保護ルート名
    }

    return back()->withErrors(['email' => '認証に失敗しました'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login.form');
    }
}

