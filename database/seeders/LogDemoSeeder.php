<?php
namespace Database\Seeders;

// database/seeders/LogDemoSeeder.php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LogDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 30日分の擬似データ
        for ($d = 0; $d < 30; $d++) {
            $date = now()->subDays($d)->format('Y-m-d');
            // 朝方にログイン失敗がやや多い
            $failCount = rand(0, 3);
            for ($i=0; $i<$failCount; $i++) {
                DB::table('auth_logs')->insert([
                    'ts'     => "$date ".sprintf('%02d:%02d:%02d', rand(8,10), rand(0,59), rand(0,59)),
                    'user_id'=> rand(2,20),
                    'role'   => 'user',
                    'result' => 'fail',
                    'reason' => 'invalid_credentials',
                    'ip'     => '203.0.113.'.rand(1,254),
                    'ua'     => 'Mozilla/5.0 Demo UA',
                ]);
            }
            // 成功ログイン・ログアウト
            DB::table('auth_logs')->insert([
                'ts'     => "$date ".sprintf('%02d:%02d:%02d', rand(9,11), rand(0,59), rand(0,59)),
                'user_id'=>  rand(2,20),
                'role'   => 'admin',
                'result' => 'success',
                'reason' => null,
                'ip'     => '198.51.100.'.rand(1,254),
                'ua'     => 'Mozilla/5.0 Demo UA',
            ]);

            // 閲覧ログ（締日前後は多めに）
            $viewBurst = (in_array((int)date('d', strtotime($date)), [24,25,26,27])) ? 5 : 2;
            for ($j=0; $j<$viewBurst; $j++) {
                $rid = Str::uuid()->toString();
                DB::table('audit_logs')->insert([
                    'ts'         => "$date ".sprintf('%02d:%02d:%02d', rand(10,18), rand(0,59), rand(0,59)),
                    'actor_id'   => rand(2,5),           // 管理者想定
                    'action'     => 'view',
                    'entity'     => 'payslip',
                    'entity_id'  => rand(1001, 1100),
                    'before_json'=> null,
                    'after_json' => null,
                    'ip'         => '203.0.113.'.rand(1,254),
                    'ua'         => 'Mozilla/5.0 Demo UA',
                    'request_id' => $rid,
                ]);
                // 同じrequest_idでPDFエクスポート（ユーザー側の例）
                DB::table('export_logs')->insert([
                    'ts'         => "$date ".sprintf('%02d:%02d:%02d', rand(10,18), rand(0,59), rand(0,59)),
                    'actor_id'   => rand(21,60),
                    'actor_role' => 'user',
                    'file_type'  => 'pdf',
                    'scope'      => 'payslip:'.date('Y-m', strtotime($date)).':user_'.rand(21,60),
                    'checksum'   => hash('sha256', Str::random(32)),
                    'path'       => '/storage/payslips/demo.pdf', // DEMOでは実ファイルにしない
                    'request_id' => $rid,
                ]);
            }

            // 変更監査（給与テーブル更新・削除を少量）
            if (rand(0,4) === 0) {
                DB::table('audit_logs')->insert([
                    'ts'         => "$date 14:".sprintf('%02d:%02d', rand(0,59), rand(0,59)),
                    'actor_id'   => rand(2,5),
                    'action'     => 'update',
                    'entity'     => 'payslip',
                    'entity_id'  => rand(1001,1100),
                    'before_json'=> json_encode(['bonus'=> 5000]),
                    'after_json' => json_encode(['bonus'=> 6000]),
                    'ip'         => '198.51.100.'.rand(1,254),
                    'ua'         => 'Mozilla/5.0 Demo UA',
                    'request_id' => Str::uuid()->toString(),
                ]);
            }

            // エラー（たまに）
            if (rand(0,8) === 0) {
                DB::table('error_logs')->insert([
                    'ts'        => "$date ".sprintf('%02d:%02d:%02d', rand(9,17), rand(0,59), rand(0,59)),
                    'level'     => 'error',
                    'message'   => 'Demo exception: payroll calc overflow',
                    'trace'     => null,
                    'request_id'=> Str::uuid()->toString(),
                ]);
            }
        }
    }
}
