<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) まずは NULL 許可でカラムを追加（制約は付けない）
        if (!Schema::hasColumn('users', 'employee_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('employee_number', 10)->nullable()->after('id');
            });
        }

        // 2) 既存ユーザーに連番で値を埋める（SP001, SP002, ...）
        $users = DB::table('users')->orderBy('id')->get(['id', 'employee_number']);
        $n = 1;
        foreach ($users as $u) {
            if (empty($u->employee_number)) {
                DB::table('users')
                    ->where('id', $u->id)
                    ->update(['employee_number' => sprintf('SP%03d', $n++)]);
            }
        }

        // 3) NOT NULL & UNIQUE を付与
        // 3-1) NOT NULL に変更
        //  → doctrine/dbal を入れているなら change() でOK。なければ Raw SQL を使う。
        if (class_exists(\Doctrine\DBAL\DriverManager::class)) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('employee_number', 10)->nullable(false)->change();
            });
        } else {
            DB::statement("ALTER TABLE users MODIFY employee_number VARCHAR(10) NOT NULL");
        }

        // 3-2) UNIQUE 付与（既に無ければ）
        // インデックス名は慣例で users_employee_number_unique
        $indexes = DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_employee_number_unique'");
        if (count($indexes) === 0) {
            DB::statement("ALTER TABLE users ADD UNIQUE KEY users_employee_number_unique (employee_number)");
        }
    }

    public function down(): void
    {
        // 制約を外してカラム削除
        if (Schema::hasColumn('users', 'employee_number')) {
            // UNIQUE があれば削除
            DB::statement("ALTER TABLE users DROP INDEX IF EXISTS users_employee_number_unique");
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('employee_number');
            });
        }
    }
};


