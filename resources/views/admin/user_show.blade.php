@extends('layouts.admin')
@section('content')
<div class="flex flex-col gap-6">

  {{-- ユーザー情報 --}}
  <div class="bg-white shadow rounded-xl p-4">
    <div class="flex justify-between items-center mb-3">
      <h1 class="font-bold">{{ $user->name }}</h1>
     <div class="flex items-center gap-2">
    {{-- 戻るボタン --}}
    <a href="{{ route('admin.home') }}" 
       class="px-3 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm">
      戻る
    </a>

    {{-- 🔍 AI説明（追加） --}}
    <button id="aiExplainBtn"
            data-url="{{ route('admin.user.ai.explain', $user) }}"
            class="px-3 py-2 border rounded hover:bg-gray-50 text-sm">
      AI説明
    </button>

    {{-- 退職ボタン --}}
    <form method="POST" action="{{ route('admin.user.destroy', $user) }}" 
          onsubmit="return confirm('退職（削除）しますか？');">
      @csrf
      @method('DELETE')
      <button class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
        退職
      </button>
    </form>
  </div>

  </div>

  {{-- 給与明細一覧 --}}
  <div class="bg-white shadow rounded-xl p-4">
    <h2 class="font-semibold mb-3">登録済み（更新日降順／3件ページング）</h2>
    <ul class="divide-y">

      @foreach($payslips as $p)
<li class="py-3 flex flex-col sm:flex-row sm:justify-between sm:items-center">
  <div class="font-medium text-gray-800">
    {{ $p->year }}年{{ $p->month }}月度　
    {{ $p->pay_type === 'salary' ? '給与' : '賞与' }}
  </div>
  <div class="text-sm text-gray-600 sm:text-right mt-2 sm:mt-0">
    支給額：<span class="font-semibold text-gray-800">￥{{ number_format($p->gross) }}</span>　
    税金：<span class="text-red-600">￥{{ number_format($p->tax) }}</span>　
    手取り：<span class="text-green-600 font-semibold">￥{{ number_format($p->net) }}</span>
  </div>
</li>
      @endforeach

    </ul>

    <div class="mt-3">
      {{ $payslips->links() }}
    </div>
  </div>
  </div> {{-- ← 一覧ブロックの閉じタグの直前 or 直後でもOK --}}

  {{-- 給与推移グラフ（過去6か月・古い順） --}}
  <div class="bg-white shadow rounded-xl p-4 mt-6">
    <h2 class="font-semibold mb-3">過去6か月の給与推移</h2>
   {{-- ★ 固定高さを付与（Tailwindなら h-64 など） --}}
  <div class="h-64">
    <canvas id="salaryChart"></canvas>
  </div>
  </div>
{{-- AI説明モーダル --}}
<div id="aiModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow max-w-xl w-[90%] p-4">
    <div class="flex items-center justify-between mb-2">
      <h2 class="font-semibold">AIの説明（このユーザーの給与トレンド）</h2>
      <button id="aiClose" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>
    <div id="aiBody" class="text-sm whitespace-pre-wrap leading-6">解析中…</div>
    <div class="text-right mt-3">
      <button id="aiClose2" class="px-3 py-1 border rounded hover:bg-gray-50">閉じる</button>
    </div>
  </div>
</div>
  {{-- Chart.js 読み込み --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  (function () {
    const labels = @json($chartLabels);
    const gross  = @json($chartGross);
    const tax    = @json($chartTax);
    const net    = @json($chartNet);

    const el = document.getElementById('salaryChart');
    if (!el) return;
    const ctx = el.getContext('2d');

    // ★ 既存インスタンスを破棄
    if (window.salaryChart instanceof Chart) {
      window.salaryChart.destroy();
    }

    window.salaryChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label: '支給額（総支給）', data: gross, borderColor: '#4b5563', backgroundColor: 'rgba(75,85,99,0.15)', tension: 0.25, fill: false, pointRadius: 3 },
          { label: '税金',             data: tax,   borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,0.15)', tension: 0.25, fill: false, pointRadius: 3 },
          { label: '手取り',           data: net,   borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,0.15)', tension: 0.25, fill: false, pointRadius: 3 },
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false, // ← 親に固定高さがあるのでOK
        // 必要ならデバッグ用に一時的にアニメ無効
        // animation: { duration: 0 },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: (v) => '¥' + Number(v ?? 0).toLocaleString() }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: (ctx) => `${ctx.dataset.label}: ¥${Number(ctx.parsed.y ?? 0).toLocaleString()}`
            }
          },
          legend: { labels: { boxWidth: 12 } }
        }
      }
    });
  })();
(async function(){
  const btn   = document.getElementById('aiExplainBtn');
  const modal = document.getElementById('aiModal');
  const body  = document.getElementById('aiBody');
  const close1 = document.getElementById('aiClose');
  const close2 = document.getElementById('aiClose2');

  if(!btn) return;

  const open  = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
  const close = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };
  [close1, close2].forEach(el => el?.addEventListener('click', close));
  modal?.addEventListener('click', (e)=>{ if(e.target===modal) close(); });

  btn.addEventListener('click', async ()=>{
    open();
    body.textContent = '解析中…';
    try {
      const res = await fetch(btn.dataset.url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        }
      });

      let data = {};
      try { data = await res.json(); } catch(_) { /* HTMLや空ならそのまま */ }

      if (!res.ok) {
        body.textContent = data.text || data.message || `エラー: HTTP ${res.status}`;
        return;
      }
      body.textContent = (data.ok)
        ? (data.text || '説明テキストなし')
        : (data.text || data.message || '取得に失敗しました。');
    } catch (_) {
      body.textContent = '通信に失敗しました。';
    }
  });
})();
</script>

</div>
@endsection
