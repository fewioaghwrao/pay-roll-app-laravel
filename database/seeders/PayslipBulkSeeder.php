<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Payslip;
use Carbon\Carbon;

class PayslipBulkSeeder extends Seeder
{
    public function run(): void
    {
        // 期間: 2023-01 ～ 2025-09
        $start = Carbon::create(2023, 1, 1);
        $end   = Carbon::create(2025, 9, 1);

        $users = User::orderBy('id')->get();

        foreach ($users as $user) {
            $isContract = trim($user->employment_type) === '契約社員';

            // 月次ループ
            for ($d = $start->copy(); $d <= $end; $d->addMonth()) {
                $year  = (int)$d->year;
                $month = (int)$d->month;

                // 月収レンジ（契約社員: 30-40万 / 正社員: 50-60万）
                [$min, $max] = $isContract ? [300000, 400000] : [500000, 600000];
                $gross = random_int($min, $max);
                $tax   = (int) round($gross * 0.10);
                $net   = $gross - $tax;

                // 見やすい更新日（その月の25日）を設定
                $ts = Carbon::create($year, $month, min(25, $d->copy()->endOfMonth()->day), 12, 0, 0);

                Payslip::updateOrCreate(
                    ['user_id' => $user->id, 'year' => $year, 'month' => $month, 'pay_type' => 'salary'],
                    [
                        'gross_amount' => $gross,
                        'tax_amount'   => $tax,
                        'net_amount'   => $net,
                        'items'        => [
                            ['label' => '基本給', 'amount' => $gross],
                            ['label' => '総税(概算10%)', 'amount' => -$tax],
                        ],
                        'created_at'   => $ts,
                        'updated_at'   => $ts,
                    ]
                );

                // 7月 & 12月は賞与 100万円（範囲内のみ）
                if (in_array($month, [7, 12], true)) {
                    // 2025/12 は範囲外なので自動的にスキップされます
                    $bonusGross = 1_000_000;
                    $bonusTax   = (int) round($bonusGross * 0.10);
                    $bonusNet   = $bonusGross - $bonusTax;

                    $tsBonus = Carbon::create($year, $month, min(20, $d->copy()->endOfMonth()->day), 12, 0, 0);

                    Payslip::updateOrCreate(
                        ['user_id' => $user->id, 'year' => $year, 'month' => $month, 'pay_type' => 'bonus'],
                        [
                            'gross_amount' => $bonusGross,
                            'tax_amount'   => $bonusTax,
                            'net_amount'   => $bonusNet,
                            'items'        => [
                                ['label' => '賞与', 'amount' => $bonusGross],
                                ['label' => '賞与税(概算10%)', 'amount' => -$bonusTax],
                            ],
                            'created_at'   => $tsBonus,
                            'updated_at'   => $tsBonus,
                        ]
                    );
                }
            }
        }
    }
}

