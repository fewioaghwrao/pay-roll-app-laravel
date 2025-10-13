<!DOCTYPE html><html lang="ja"><head><meta charset="utf-8"><style>
/* ローカルフォント設定 */
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

/* スタイル設定 */
body {
  font-family: 'NotoSansJP', sans-serif;
  font-size: 12px;
  margin: 20px;
}
.table {
  width: 100%;
  border-collapse: collapse;
}
.table th, .table td {
  border: 1px solid #555;
  padding: 6px;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
}
.hd {
  font-size: 16px;
  font-weight: bold;
}
.user-info {
  text-align: right;
  font-size: 12px;
  line-height: 1.4;
}
</style>
</head><body>

<div class="header">
  <div class="hd">{{ $ws->year }}年分 源泉徴収票</div>
  @if(!empty($currentUser))
  <div class="user-info">
    <div>{{ $currentUser->name }} 様</div>
    @if(!empty($currentUser->employee_number))
      <div>社員番号：{{ $currentUser->employee_number }}</div>
    @endif
  </div>
  @endif
</div>

<table class="table">
  <tr>
    <th>支払金額</th>
    <td>¥{{ number_format($ws->total_salary ?? 0) }}</td>
  </tr>
  <tr>
    <th>源泉徴収税額</th>
    <td>¥{{ number_format($ws->total_tax ?? 0) }}</td>
  </tr>
  <tr>
    <th>社会保険料等の金額</th>
    <td>¥{{ number_format($ws->social_insurance ?? 0) }}</td>
  </tr>
  <tr>
    <th>所得控除後の金額</th>
    <td>¥{{ number_format($ws->net_income ?? 0) }}</td>
  </tr>
  @if(!empty($ws->remarks))
  <tr>
    <th>備考</th>
    <td>{{ $ws->remarks }}</td>
  </tr>
  @endif
</table>

@if(!empty($ws->meta))
  <h3 style="margin-top:16px;">付記</h3>
  <table class="table">
    @foreach($ws->meta as $k => $v)
      <tr>
        <th>{{ $k }}</th>
        <td>{{ is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE) }}</td>
      </tr>
    @endforeach
  </table>
@endif

</body></html>

