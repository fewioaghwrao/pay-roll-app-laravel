<?php
namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\User; use App\Models\Payslip; use App\Models\WithholdingStatement;
use Illuminate\Http\Request;
use Carbon\Carbon; // 先頭に追加


class HomeController extends Controller
{

public function index(Request $request)
{
    $q  = trim((string)$request->query('q'));
    $et = $request->query('employment_type'); // 任意

    $users = User::query()
        // 検索：社員番号 or 氏名（前方・部分一致）
        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('employee_number', 'like', $q.'%') // 前方一致（例：100 で 100xx を拾う）
                         ->orWhere('name', 'like', '%'.$q.'%');      // 部分一致
            });
        })
        // 追加フィルタ：雇用形態（任意）
        ->when($et, fn($query) => $query->where('employment_type', $et))
        // 並び順（例：社員番号昇順）
        ->orderBy('employee_number')
        ->paginate(5)
        // 検索条件をページネーションリンクに引き継ぐ
        ->appends($request->query());

    return view('admin.home', compact('users'));
}

public function showUser(User $user)
{
    $payslips = Payslip::where('user_id', $user->id)
        ->orderByDesc('updated_at')
        ->paginate(3);

    // 一覧用はそのまま
    $payslips->setCollection(
        $payslips->getCollection()->transform(function ($p) {
            $p->gross = $p->gross_amount;
            $p->tax   = isset($p->tax_amount) ? $p->tax_amount : max(0, (int)$p->gross_amount - (int)($p->net_amount ?? 0));
            $p->net   = isset($p->net_amount) ? $p->net_amount : max(0, (int)$p->gross_amount - (int)($p->tax_amount ?? 0));
            return $p;
        })
    );

    /** ---------------------------
     *  グラフ用：常に「最新の給与月」から過去6か月ぶんを生成
     * --------------------------- */
 // 全明細（辞書化用）
$all = Payslip::where('user_id', $user->id)
    ->orderByDesc('year')->orderByDesc('month')
    ->get();

// ページ内の最古（年/月の小さい方）を起点にする
$oldestInPage = $payslips->getCollection()
    ->sortBy(fn($p) => $p->year * 100 + $p->month)
    ->first();

// 起点（月）
$base = $oldestInPage
    ? \Carbon\Carbon::createFromDate($oldestInPage->year, $oldestInPage->month, 1)
    : (
        // ページが空なら DB の最古 or 今月
        ($all->last()
            ? \Carbon\Carbon::createFromDate($all->last()->year, $all->last()->month, 1)
            : now()->startOfMonth())
      );

// YYYYMM => レコード辞書
$dict = $all->keyBy(fn($p) => sprintf('%04d%02d', $p->year, $p->month));

// ★ ここが肝：過去4〜未来2（合計7点）、古い→新しい順で作る
$chartLabels = [];
$chartGross  = [];
$chartTax    = [];
$chartNet    = [];

for ($offset = -4; $offset <= 2; $offset++) {
    $dt    = $base->copy()->addMonths($offset);
    $key   = $dt->format('Ym');
    $label = $dt->format('Y/m'); // 横軸

    if (isset($dict[$key])) {
        $p     = $dict[$key];
        $gross = (int)($p->gross_amount ?? 0);
        $tax   = isset($p->tax_amount) ? (int)$p->tax_amount : max(0, $gross - (int)($p->net_amount ?? 0));
        $net   = isset($p->net_amount) ? (int)$p->net_amount : max(0, $gross - (int)($p->tax_amount ?? 0));
    } else {
        // 欠損月の穴埋め（線を切りたいなら 0 → null に変更）
        $gross = 0;
        $tax   = 0;
        $net   = 0;
    }

    $chartLabels[] = $label;
    $chartGross[]  = $gross;
    $chartTax[]    = $tax;
    $chartNet[]    = $net;
}

    return view('admin.user_show', compact(
        'user', 'payslips',
        'chartLabels', 'chartGross', 'chartTax', 'chartNet'
    ));
}



public function createPayslip(Request $request, User $user)
{
$data = $request->validate([
'year'=>'required|integer|min:2000',
'month'=>'required|integer|min:1|max:12',
'pay_type'=>'required|in:salary,bonus',
'gross_amount'=>'required|integer',
'tax_amount'=>'nullable|integer',
'net_amount'=>'required|integer',
'items'=>'nullable',
]);
$data['user_id'] = $user->id;
if (isset($data['items']) && is_string($data['items'])) {
$data['items'] = json_decode($data['items'], true);
}
Payslip::updateOrCreate([
'user_id'=>$user->id,
'year'=>$data['year'],
'month'=>$data['month'],
'pay_type'=>$data['pay_type'],
], $data);
return back()->with('status','給与（賞与）を登録しました');
}


public function createWithholding(Request $request, User $user)
{
$data = $request->validate([
'year'=>'required|integer|min:2000',
'total_paid'=>'required|integer',
'total_tax'=>'required|integer',
'meta'=>'nullable',
]);
if (isset($data['meta']) && is_string($data['meta'])) {
$data['meta'] = json_decode($data['meta'], true);
}
WithholdingStatement::updateOrCreate([
'user_id'=>$user->id,
'year'=>$data['year'],
], [
'total_paid'=>$data['total_paid'],
'total_tax'=>$data['total_tax'],
'meta'=>$data['meta'] ?? null,
]);
return back()->with('status','源泉徴収票を登録しました');
}


}