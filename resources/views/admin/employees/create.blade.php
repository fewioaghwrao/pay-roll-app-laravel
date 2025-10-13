{{-- resources/views/admin/users/create.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="bg-white shadow rounded-xl p-4 max-w-2xl mx-auto">
  <div class="flex items-center justify-between mb-4">
    <h1 class="font-bold">社員登録</h1>
    <a href="{{ route('admin.home') }}" class="text-sm text-gray-600 hover:underline">戻る</a>
  </div>

  @if ($errors->any())
    <div class="mb-4 p-3 rounded bg-red-50 text-red-700 text-sm">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.employee.store') }}" class="space-y-4">
    @csrf

    <div>
      <label class="block text-sm text-gray-700">社員番号 <span class="text-red-500">*</span></label>
      <input type="text" name="employee_number" value="{{ old('employee_number') }}"
             class="mt-1 w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block text-sm text-gray-700">氏名 <span class="text-red-500">*</span></label>
      <input type="text" name="name" value="{{ old('name') }}"
             class="mt-1 w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block text-sm text-gray-700">メールアドレス <span class="text-red-500">*</span></label>
      <input type="email" name="email" value="{{ old('email') }}"
             class="mt-1 w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block text-sm text-gray-700">パスワード <span class="text-red-500">*</span></label>
      <input type="password" name="password"
             class="mt-1 w-full border rounded px-3 py-2" required>
      <p class="text-xs text-gray-500 mt-1">※英字・数字を含む8文字以上</p>
    </div>

    <div>
      <label class="block text-sm text-gray-700">パスワード（確認） <span class="text-red-500">*</span></label>
      <input type="password" name="password_confirmation"
             class="mt-1 w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block text-sm text-gray-700">雇用形態 <span class="text-red-500">*</span></label>
      <select name="employment_type" class="mt-1 w-full border rounded px-3 py-2" required>
        @foreach($employmentTypes as $t)
          <option value="{{ $t }}" @selected(old('employment_type') === $t)>{{ $t }}</option>
        @endforeach
      </select>
    </div>

    <div class="pt-2 flex justify-end gap-2">
      <a href="{{ route('admin.home') }}" class="px-3 py-2 border rounded">キャンセル</a>
      <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
        登録する
      </button>
    </div>
  </form>
</div>
@endsection
