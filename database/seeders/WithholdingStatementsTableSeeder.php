<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WithholdingStatement;
use App\Models\User;

class WithholdingStatementsTableSeeder extends Seeder
{
    public function run(): void
    {
        $years = [2023, 2024];
        $users = User::all();

        foreach ($users as $user) {
            foreach ($years as $year) {
                WithholdingStatement::updateOrCreate(
                    ['user_id' => $user->id, 'year' => $year],
                    [
                        'total_salary'   => rand(4800000, 7200000),  // 年収例：480〜720万
                        'total_tax'      => rand(300000, 600000),   // 源泉徴収税額
                        'social_insurance' => rand(600000, 900000), // 社会保険料等
                        'net_income'     => rand(3800000, 5800000), // 手取り
                        'remarks'        => '自動生成データ（テスト用）',
                    ]
                );
            }
        }
    }
}

