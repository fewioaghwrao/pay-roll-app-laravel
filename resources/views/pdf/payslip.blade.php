<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<style>
/* ===== フォント設定 ===== */
@font-face{
  font-family: 'NotoSansJP';
  font-weight: normal;
  font-style: normal;
  src: url('{{ storage_path('fonts/NotoSansJP-Regular.ttf') }}') format('truetype');
}
@font-face{
  font-family: 'NotoSansJP';
  font-weight: bold;
  font-style: normal;
  src: url('{{ storage_path('fonts/NotoSansJP-Bold.ttf') }}') format('truetype');
}

/* ===== 共通スタイル ===== */
body {
  font-family: 'NotoSansJP', sans-serif;
  font-size: 12px;
  margin: 20px;
}
.table {
  width: 100%;
  border-collapse: collapse;
}
.table th,
.table td {
  border: 1px solid #555;
  padding: 6px;
  text-align: left;
}
.hd {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 10px;
}

/* ===== 赤背景＋白文字ヘッダー ===== */
.table th.header {
  background-color: #d32f2f; /* 赤系 */
  color: #fff;
  text-align: center;
}

/* ===== 内訳テーブル見出し ===== */
.table th.subheader {
  background-color: #c62828; /* 少し濃い赤 */
  color: #fff;
  text-align: center;
}
</style>
</head>

<body>
<div class="hd">
  {{ $payslip->year }}年{{ $payslip->month }}月度　
  {{ $payslip->pay_type==='salary' ? '給与' : '賞与' }} 明細
  @if(!empty($currentUser))
    <span style="font-size:12px; font-weight:normal; margin-left:20px;">
      （{{ $currentUser->name }}）
    </span>
  @endif
</div>

<!-- === 総支給・税額・手取り === -->
<table class="table">
  <tr>
    <th class="header">総支給</th>
    <td>¥{{ number_format($payslip->gross_amount) }}</td>
    <th class="header">税額</th>
    <td>¥{{ number_format($payslip->tax_amount) }}</td>
    <th class="header">手取り</th>
    <td>¥{{ number_format($payslip->net_amount) }}</td>
  </tr>
</table>

<!-- === 内訳 === -->
@if(!empty($payslip->items))
<h3 style="margin-top: 20px;">内訳</h3>
<table class="table">
  <tr>
    <th class="subheader">項目</th>
    <th class="subheader">金額</th>
  </tr>
  @foreach($payslip->items as $r)
  <tr>
    <td>{{ $r['label'] ?? '' }}</td>
    <td>¥{{ number_format($r['amount'] ?? 0) }}</td>
  </tr>
  @endforeach
</table>
@endif
</body>
</html>
