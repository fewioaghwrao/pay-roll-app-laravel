<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use App\Services\AiExplanationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayslipAiController extends Controller
{
    public function __construct(private AiExplanationService $svc) {}

    public function explain(Request $request, Payslip $payslip)
    {
        // 認可: 本人の明細のみ
        abort_if($payslip->user_id !== Auth::id(), 403);

        // 監査ログ（PII無し）
        try {
            DB::table('audit_logs')->insert([
                'ts' => now(),
                'actor_id' => Auth::id(),
                'action' => 'ai_explain',
                'entity' => 'payslip',
                'entity_id' => $payslip->id,
                'ip' => $request->ip(),
                'ua' => substr((string) $request->userAgent(), 0, 255),
                'request_id' => (string) \Illuminate\Support\Str::uuid(),
            ]);
        } catch (\Throwable $e) {
            // ログ失敗は握りつぶす
        }

        // マスクした最小情報のみAIへ
        $payload = [
            'year'         => $payslip->year,
            'month'        => $payslip->month,
            'pay_type'     => $payslip->pay_type, // salary/bonus
            'gross_amount' => (int) $payslip->gross_amount,
            'tax_amount'   => (int) $payslip->tax_amount,
            'net_amount'   => (int) $payslip->net_amount,
            'items'        => collect($payslip->items ?? [])
                                ->map(fn($r) => ['label' => $r['label'] ?? '-', 'amount' => (int)($r['amount'] ?? 0)])
                                ->values()->all(),
        ];

        try {
            $text = $this->svc->explain($payload);
            return response()->json(['ok' => true, 'text' => $text]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'text' => "AI説明を取得できませんでした。時間をおいて再実行してください。",
            ], 500);
        }
    }
}
