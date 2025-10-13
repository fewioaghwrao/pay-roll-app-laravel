<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AiExplanationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAiController extends Controller
{
    public function __construct(private AiExplanationService $svc) {}

    public function explain(Request $request, User $user)
    {
        // ここでは権限制御は省略。必要なら Gate/Policy で。

        // 直近6件の手取り推移（例）
$rows = DB::table('payslips')
    ->select('id','year','month','pay_type','gross_amount','tax_amount','net_amount')
    ->where('user_id', $user->id)
    ->orderByDesc('year')->orderByDesc('month')
    ->limit(6)->get()->reverse()->values();

// ラベルと系列（古い→新しい）
$labels = [];
$gross  = [];
$tax    = [];
$net    = [];
$hasBonus = false;

foreach ($rows as $r) {
    $labels[] = sprintf('%d/%02d', $r->year, $r->month);
    $gross[]  = (int)$r->gross_amount;
    $tax[]    = (int)$r->tax_amount;
    $net[]    = (int)$r->net_amount;
    if ($r->pay_type === 'bonus') $hasBonus = true;
}

$payload = [
    'series' => [
        'labels' => $labels,
        'gross'  => $gross,
        'tax'    => $tax,
        'net'    => $net,
        'has_bonus' => $hasBonus,
    ],
    // 直近月のスナップショット（最後の要素）
    'year'         => optional($rows->last())->year ?? now()->year,
    'month'        => optional($rows->last())->month ?? now()->month,
    'pay_type'     => optional($rows->last())->pay_type ?? 'salary',
    'gross_amount' => (int)(optional($rows->last())->gross_amount ?? 0),
    'tax_amount'   => (int)(optional($rows->last())->tax_amount ?? 0),
    'net_amount'   => (int)(optional($rows->last())->net_amount ?? 0),
];

        try {
            $text = $this->svc->explain($payload);
            return response()->json(['ok' => true, 'text' => $text]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'text' => 'AI説明を取得できませんでした。時間をおいて再実行してください。',
            ], 500);
        }
    }
}

