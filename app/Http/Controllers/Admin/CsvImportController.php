<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Exception as CsvException;

class CsvImportController extends Controller
{
    /**
     * 画面表示（ドロップゾーン）
     */
    public function create()
    {
        return view('admin.csv-import');
    }

    /**
     * CSV取り込み（AJAX/Dropzone想定）
     */
public function store(Request $request)
{
    $request->validate([
        'file' => ['required','file','mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel','max:10240'],
    ]);

    try {
        $path = $request->file('file')->getRealPath();

        $csv = \League\Csv\Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);
        $headers = $csv->getHeader();

        $required = ['employee_number', 'year', 'month', 'basic_pay', 'allowances', 'deductions', 'pay_type'];
        $missing  = array_diff($required, $headers);
        if (!empty($missing)) {
            return response()->json([
                'ok' => false,
                'message' => 'CSVヘッダが不足しています',
                'missing_headers' => array_values($missing),
            ], 422);
        }

        $records = \League\Csv\Statement::create()->process($csv);

        $rowNum = 1;
        $okCount = 0;
        $errors = [];

        DB::beginTransaction();

        foreach ($records as $row) {
            $rowNum++;

            // バリデーション（yearはYEAR型だがintでOK）
            $v = Validator::make($row, [
                'employee_number' => ['required','string','max:50'],
                'year'            => ['required','integer','digits:4'],
                'month'           => ['required','integer','between:1,12'],
                'basic_pay'       => ['required','numeric','min:0'],
                'allowances'      => ['nullable','numeric','min:0'],
                'deductions'      => ['nullable','numeric','min:0'],
                'tax_amount'      => ['nullable','numeric','min:0'],
                'pay_type'        => ['required','in:salary,bonus'],
            ]);

            if ($v->fails()) {
                $errors[] = ['row'=>$rowNum,'messages'=>$v->errors()->all()];
                continue;
            }

            try {
                // 社員番号一致の既存ユーザーのみ対象
                $user = DB::table('users')->where('employee_number', $row['employee_number'])->first();
                if (!$user) {
                    $errors[] = ['row'=>$rowNum,'messages'=>['社員番号が未登録のためスキップ: '.$row['employee_number']]];
                    continue;
                }

                // 数値化
                $basic      = (int)$row['basic_pay'];
                $allowances = (int)($row['allowances'] ?? 0);
                $deductions = (int)($row['deductions'] ?? 0);
                $tax        = (int)($row['tax_amount'] ?? 0);

                $gross = $basic + $allowances;              // 総支給
                $net   = $gross - $tax - $deductions;       // 手取り

                // 明細の内訳を items(JSON) に格納（任意で拡張可）
                $items = json_encode([
                    'basic_pay'  => $basic,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'tax_amount' => $tax,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                // user_id + year + month + pay_type で upsert
                DB::table('payslips')->updateOrInsert(
                    [
                        'user_id'  => $user->id,
                        'year'     => (int)$row['year'],
                        'month'    => (int)$row['month'],
                        'pay_type' => $row['pay_type'],
                    ],
                    [
                        'gross_amount'   => $gross,
                        'tax_amount'     => $tax,
                        'net_amount'     => $net,
                        'items'          => $items,
                        // 監査/トレース用に冗長保持（スキーマにカラムあり）
                        'employee_number'=> $row['employee_number'],
                        'updated_at'     => now(),
                        'created_at'     => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );

                $okCount++;
            } catch (\Throwable $e) {
                $errors[] = ['row'=>$rowNum,'messages'=>['DBエラー: '.$e->getMessage()]];
            }
        }

        if ($okCount === 0 || (count($errors) > 0 && $okCount < count($errors))) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        return response()->json([
            'ok'       => $okCount > 0,
            'imported' => $okCount,
            'errors'   => $errors,
            'message'  => $okCount > 0
                ? "取り込み完了：{$okCount}件 / エラー：".count($errors)."件"
                : "取り込み失敗（全件エラー）",
        ], $okCount > 0 ? 200 : 422);

    } catch (\League\Csv\Exception $e) {
        return response()->json([
            'ok' => false,
            'message' => 'CSVの読み込みに失敗しました: '.$e->getMessage(),
        ], 422);
    }
}


}
