@extends('layouts.app')
@section('content')
<div class="flex flex-col gap-6">
<div class="bg-white shadow rounded-xl p-4">
<div class="flex justify-between items-center mb-2">
  <h2 class="font-semibold">源泉徴収票 PDF</h2>

  @if(!empty($currentUser))
    <div class="flex items-center gap-3 text-sm text-gray-600">
      <div>
        ログイン中：
        <span class="font-semibold">{{ $currentUser->name }}</span>
        社員番号：
        <span class="font-semibold">{{ $currentUser->employee_number }}</span>
      </div>

      {{-- 🔹 ログアウトボタン追加 --}}
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button
          type="submit"
          class="px-3 py-1 border rounded hover:bg-gray-100 transition">
          ログアウト
        </button>
      </form>
    </div>
  @endif
</div>


<form method="GET" action="{{ route('withholding.pdf') }}" class="flex gap-2 items-end flex-wrap">
  <label class="block">
    <span class="text-sm">年度選択</span>
    <select name="year" class="border rounded p-2">
      @foreach($years as $y)
        <option value="{{ $y }}">{{ $y }}年分</option>
      @endforeach
    </select>
  </label>
  <button class="bg-indigo-600  text-white px-4 py-2 rounded">PDFで保存</button>
</form>
</div>


<div class="bg-white shadow rounded-xl p-4">
<div class="flex justify-between items-center mb-3">
<h2 class="font-semibold">給与・賞与一覧（更新日降順/3件ページング）</h2>
<div class="flex gap-2 text-sm">
  <a
    class="px-3 py-1 rounded border transition 
           {{ !$type ? 'bg-green-200 text-black' : 'hover:bg-green-100' }}"
    href="{{ route('user.home') }}">
    すべて
  </a>

  <a
    class="px-3 py-1 rounded border transition 
           {{ $type === 'salary' ? 'bg-green-200 text-black' : 'hover:bg-green-100' }}"
    href="{{ route('user.home', ['type' => 'salary']) }}">
    給与のみ
  </a>

  <a
    class="px-3 py-1 rounded border transition 
           {{ $type === 'bonus' ? 'bg-green-200 text-black' : 'hover:bg-green-100' }}"
    href="{{ route('user.home', ['type' => 'bonus']) }}">
    賞与のみ
  </a>
</div>

</div>


<ul class="divide-y">
@foreach($payslips as $p)
<li class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
<div>
<div class="font-medium">{{ $p->year }}年{{ $p->month }}月度 {{ $p->pay_type==='salary' ? '給与' : '賞与' }}</div>
</div>
<div class="flex gap-2">
<a href="{{ route('payslip.show',$p) }}" class="px-3 py-2 border rounded">明細</a>
<a href="{{ route('payslip.pdf',$p) }}" class="px-3 py-2 bg-indigo-600 text-white rounded">PDFで保存</a>
</div>
</li>
@endforeach
</ul>


 <div class="mt-3">{{ $payslips->links() }}</div> 
</div>
</div>
@endsection