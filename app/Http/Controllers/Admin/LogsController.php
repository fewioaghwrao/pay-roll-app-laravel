<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogsController extends Controller
{
    /**
     * ログ種別:
     * auth        = 認証/認可ログ（ログイン/ログアウト/失敗/403等）
     * view        = 個人情報アクセス（閲覧）ログ
     * audit       = データ変更監査（Before/After）
     * error       = エラー/例外ログ
     * user_pdf    = ユーザー側PDFダウンロード/生成ログ
     */
    public function index(Request $request)
    {
        $type = $request->string('type', 'auth')->toString(); // デフォルトauth
        $perPage = 20;

        // 共通フィルタ（期間/ユーザー）
        $dateFrom = $request->date('from');
        $dateTo   = $request->date('to');
        $actorId  = $request->input('actor_id');

        // ベースクエリを種別ごとに切り替え
        switch ($type) {
            case 'auth':
                // 例: auth_logs(ts, user_id, role, ip, ua, result, reason)
                $query = DB::table('auth_logs')->orderByDesc('ts');
                break;

            case 'view':
                // 例: audit_logs(action='view', entity='payslip'等)
                $query = DB::table('audit_logs')
                    ->where('action', 'view')
                    ->orderByDesc('ts');
                break;

            case 'audit':
                // 例: audit_logs(action in create/update/delete)
                $query = DB::table('audit_logs')
                    ->whereIn('action', ['create', 'update', 'delete'])
                    ->orderByDesc('ts');
                break;

            case 'error':
                // 例: error_logs(ts, level, message, trace, request_id)
                $query = DB::table('error_logs')->orderByDesc('ts');
                break;

            case 'user_pdf':
                // 例: export_logs(actor_role='user', file_type='pdf')
                $query = DB::table('export_logs')
                    ->where('file_type', 'pdf')
                    ->where('actor_role', 'user') // カラムが無い場合はactor_idからJOINで判定してもOK
                    ->orderByDesc('ts');
                break;

            default:
                $type = 'auth';
                $query = DB::table('auth_logs')->orderByDesc('ts');
        }

        // フィルタ適用
        if ($dateFrom) $query->where('ts', '>=', $dateFrom->format('Y-m-d 00:00:00'));
        if ($dateTo)   $query->where('ts', '<=', $dateTo->format('Y-m-d 23:59:59'));
        if ($actorId)  $query->where(function ($q) use ($type, $actorId) {
            // テーブルにより列名が違うため分岐
            if (in_array($type, ['auth'])) {
                $q->where('user_id', $actorId);
            } elseif (in_array($type, ['view', 'audit'])) {
                $q->where('actor_id', $actorId);
            } elseif ($type === 'user_pdf') {
                $q->where('actor_id', $actorId);
            } elseif ($type === 'error') {
                // error_logs はactor_id無い想定が多いので何もしない
            }
        });

        $logs = $query->paginate($perPage)->appends($request->query());

        return view('admin.logs.index', [
            'type' => $type,
            'logs' => $logs,
            'filters' => [
                'from' => optional($dateFrom)?->format('Y-m-d'),
                'to'   => optional($dateTo)?->format('Y-m-d'),
                'actor_id' => $actorId,
            ],
        ]);
    }
}
