<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.employees.index', compact('users'));
    }

    public function create()
    {
         $employmentTypes = ['正社員', '契約社員'];
         return view('admin.employees.create', compact('employmentTypes'));
    }

    public function store(Request $request)
    {
    $employmentTypes = ['正社員', '契約社員'];

    $validated = $request->validate([
        'employee_number' => ['required', 'string', 'max:50', 'unique:users,employee_number'],
        'name'            => ['required', 'string', 'max:255'],
        'email'           => ['required', 'email', 'max:255', 'unique:users,email'],
        'password'        => ['required', 'min:8', 'confirmed'],
        'employment_type' => ['required', Rule::in($employmentTypes)],
    ]);

    User::create([
        'employee_number' => $validated['employee_number'],
        'name'            => $validated['name'],
        'email'           => $validated['email'],
        'password'        => bcrypt($validated['password']),
        'employment_type' => $validated['employment_type'], // カラムがある場合
    ]);

    return redirect()->route('admin.employee.index')->with('status', '社員を登録しました。');
    }
}
