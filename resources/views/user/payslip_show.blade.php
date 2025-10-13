@extends('layouts.app')
@section('content')
<div class="bg-white shadow rounded-xl p-4">
<div class="flex justify-between items-center mb-4">
     <a href="{{ route('user.home') }}"
       class="inline-flex items-center gap-1 px-3 py-2 border rounded hover:bg-gray-50"
       aria-label="ホームに戻る">
      {{-- 矢印アイコン（SVG） --}}
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span>戻る</span>
    </a>
  <h1 class="font-bold">
    {{ $payslip->year }}年{{ $payslip->month }}月度 {{ $payslip->pay_type==='salary' ? '給与' : '賞与' }} 明細
  </h1>
 
  <div class="flex gap-2">
    {{-- 🔍 AIで説明 --}}
    <button id="aiExplainBtn"
            class="px-3 py-2 border rounded hover:bg-gray-50">
      AIで説明
    </button>

    <a href="{{ route('payslip.pdf',$payslip) }}"
       class="px-3 py-2 bg-indigo-600 text-white rounded">PDFで保存</a>
  </div>
</div>

{{-- モーダル --}}
<div id="aiModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow max-w-xl w-[90%] p-4">
    <div class="flex items-center justify-between mb-2">
      <h2 class="font-semibold">AIの説明</h2>
      <button id="aiClose" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>
    <div id="aiBody" class="text-sm whitespace-pre-wrap leading-6">
      解析中…
    </div>
    <div class="text-right mt-3">
      <button id="aiClose2" class="px-3 py-1 border rounded hover:bg-gray-50">閉じる</button>
    </div>
  </div>
</div>

<dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
<div><dt class="text-gray-500">総支給</dt><dd class="font-medium">¥{{ number_format($payslip->gross_amount) }}</dd></div>
<div><dt class="text-gray-500">税額</dt><dd class="font-medium">¥{{ number_format($payslip->tax_amount) }}</dd></div>
<div><dt class="text-gray-500">手取り</dt><dd class="font-medium">¥{{ number_format($payslip->net_amount) }}</dd></div>
<div><dt class="text-gray-500">更新日</dt><dd class="font-medium">{{ $payslip->updated_at->format('Y-m-d') }}</dd></div>
</dl>
@if(!empty($payslip->items))
<h2 class="mt-4 font-semibold">内訳</h2>
<table class="w-full text-sm mt-2 border">
<thead class="bg-gray-100">
<tr><th class="p-2 text-left">項目</th><th class="p-2 text-right">金額</th></tr>
</thead>
<tbody>
@foreach($payslip->items as $row)
<tr class="border-t"><td class="p-2">{{ $row['label'] ?? '' }}</td><td class="p-2 text-right">¥{{ number_format($row['amount'] ?? 0) }}</td></tr>
@endforeach
</tbody>
</table>
@endif
</div>
<script>
(function(){
  const btn = document.getElementById('aiExplainBtn');
  const modal = document.getElementById('aiModal');
  const body = document.getElementById('aiBody');
  const close1 = document.getElementById('aiClose');
  const close2 = document.getElementById('aiClose2');

  function open(){ modal.classList.remove('hidden'); modal.classList.add('flex'); }
  function close(){ modal.classList.add('hidden'); modal.classList.remove('flex'); }

  close1.addEventListener('click', close);
  close2.addEventListener('click', close);
  modal.addEventListener('click', (e)=>{ if(e.target===modal) close(); });

  btn.addEventListener('click', async ()=>{
    open();
    body.textContent = '解析中…';
    try {
      const url = "{{ route('payslip.ai.explain', $payslip) }}";
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
      });
      const data = await res.json();
      if(data.ok){ body.textContent = data.text; }
      else{ body.textContent = data.text || '取得に失敗しました。'; }
    } catch (e) {
      body.textContent = '通信に失敗しました。';
    }
  });
})();
</script>
@endsection