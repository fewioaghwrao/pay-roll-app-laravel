@extends('layouts.admin')

@section('content')
<div class="bg-white shadow rounded-xl p-6">
  <h1 class="text-xl font-bold mb-4">CSV取り込み（ドラッグ＆ドロップ対応）</h1>

  <p class="text-sm text-gray-600 mb-4">
    対応フォーマット（ヘッダ行必須）：<br>
    <code>1行目はemployee_number,year,month,basic_pay,allowances,deductions,tax_amount,pay_type</code><br>
    <code>2行目以降は社員番号,月,年,基本給,手当,差引額,税額,給与もしくは賞与</code><br>
    <code>を記載してください。</code><br>
  </p>

  {{-- 成果メッセージ --}}
  <div id="result" class="hidden border rounded p-3 mb-4"></div>

  {{-- Dropzone フォーム --}}
  <form action="{{ route('admin.csv.store') }}" 
        method="post" 
        class="dropzone" 
        id="csv-dropzone" 
        enctype="multipart/form-data">
      @csrf
<div class="dz-message text-red-600" data-dz-message>
  <span>ここにCSVファイルをドラッグ＆ドロップ、またはクリックして選択</span>
</div>
  </form>

  <ul class="text-xs text-gray-500 mt-4 list-disc ml-5 space-y-1">
    <li>最大サイズ：10MB</li>
    <li>1ファイルのみ対応</li>
  </ul>
</div>
@endsection

@push('scripts')
  {{-- Dropzone.js CDN（簡易版） --}}
  <script src="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone-min.js"></script>
  <link href="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone-min.css" rel="stylesheet"/>

  <script>
    // Dropzone オプション
    Dropzone.autoDiscover = false;

    const dz = new Dropzone("#csv-dropzone", {
      paramName: "file",
      maxFiles: 1,
      maxFilesize: 10, // MB
      acceptedFiles: ".csv,text/csv",
      addRemoveLinks: true,
      dictDefaultMessage: "ここにCSVをドロップ",
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}",
        'Accept': 'application/json'
      },
      init: function () {
        this.on("success", function (file, resp) {
          showResult(true, resp);
        });
        this.on("error", function (file, errorMsg, xhr) {
          // Laravelの422 JSONを拾う
          try {
            if (xhr && xhr.responseText) {
              const data = JSON.parse(xhr.responseText);
              showResult(false, data);
            } else {
              showResult(false, { message: errorMsg || "取り込み失敗" });
            }
          } catch (e) {
            showResult(false, { message: "取り込み失敗" });
          }
        });
        this.on("maxfilesexceeded", function(file) {
          this.removeAllFiles();
          this.addFile(file);
        });
      }
    });

    function showResult(ok, data) {
      const box = document.getElementById('result');
      box.classList.remove('hidden');
      box.classList.remove('border-green-400','bg-green-50','text-green-800');
      box.classList.remove('border-red-400','bg-red-50','text-red-800');

      if (ok) {
        box.classList.add('border-green-400','bg-green-50','text-green-800');
        box.innerHTML = `
          <div class="font-semibold mb-1">${data.message ?? '取り込み完了'}</div>
          <div>成功件数: <b>${data.imported ?? '-'}</b></div>
          ${renderErrors(data.errors)}
        `;
      } else {
        box.classList.add('border-red-400','bg-red-50','text-red-800');
        box.innerHTML = `
          <div class="font-semibold mb-1">${data.message ?? '取り込み失敗'}</div>
          ${renderErrors(data.errors)}
          ${data.missing_headers ? '<div class="mt-2">不足ヘッダ: <code>' + data.missing_headers.join(', ') + '</code></div>' : ''}
        `;
      }
    }

    function renderErrors(errors) {
      if (!errors || !errors.length) return '';
      const items = errors.slice(0, 50).map(e => {
        const msgs = (e.messages || []).map(m => `・${m}`).join('<br>');
        return `<li>行 ${e.row}:<br>${msgs}</li>`;
      }).join('');
      return `<details class="mt-2"><summary>エラー詳細（最大50件表示）</summary><ul class="list-disc ml-6 text-sm mt-1">${items}</ul></details>`;
    }
  </script>
@endpush
