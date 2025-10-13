<?php
// app/Http/Requests/StoreEmployeeRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
          // 管理者ガードでログインしているか
         return auth('admin')->check();
        //return $this->user()?->can('admin') ?? false; // 権限はプロジェクトに合わせて
    }

    public function rules(): array
    {
        return [
            'employee_number' => ['required','string','max:50','unique:users,employee_number'],
            'name'            => ['required','string','max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'        => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers()
            ],
            'employment_type' => ['required','in:正社員,契約社員'], // 「ケイ役社員」は「契約社員」の想定で実装
        ];
    }

    public function attributes(): array
    {
        return [
            'employee_number' => '社員番号',
            'name'            => '氏名',
            'email'           => 'メールアドレス',
            'password'        => 'パスワード',
            'employment_type' => '雇用形態',
        ];
    }
}
