<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
    $this->call([
        WithholdingStatementsTableSeeder::class,
    ]);

$u = \App\Models\User::factory()->create([
'name'=>'山田 太郎','email'=>'taro@example.com','password'=>bcrypt('password'),
'employment_type'=>'正社員',
]);


\App\Models\Payslip::factory()->create([
'user_id'=>$u->id,'year'=>2025,'month'=>7,'pay_type'=>'salary',
'gross_amount'=>300000,'tax_amount'=>30000,'net_amount'=>270000,
'items'=>[['label'=>'基本給','amount'=>250000],['label'=>'残業','amount'=>50000]],
]);


\App\Models\WithholdingStatement::create([
'user_id'=>$u->id,'year'=>2025,'total_paid'=>3600000,'total_tax'=>360000,
'meta'=>['company'=>'サンプル株式会社']
]);
    }
}

