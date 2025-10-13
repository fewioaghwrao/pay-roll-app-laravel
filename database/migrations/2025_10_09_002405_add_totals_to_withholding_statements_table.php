<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withholding_statements', function (Blueprint $table) {
            // すでに存在しない場合のみ追加（再実行に強い）
            if (!Schema::hasColumn('withholding_statements', 'total_salary')) {
                $table->unsignedBigInteger('total_salary')->default(0)->after('year');
            }
            if (!Schema::hasColumn('withholding_statements', 'total_tax')) {
                $table->unsignedBigInteger('total_tax')->default(0)->after('total_salary');
            }
            if (!Schema::hasColumn('withholding_statements', 'social_insurance')) {
                $table->unsignedBigInteger('social_insurance')->default(0)->after('total_tax');
            }
            if (!Schema::hasColumn('withholding_statements', 'net_income')) {
                $table->unsignedBigInteger('net_income')->default(0)->after('social_insurance');
            }
            if (!Schema::hasColumn('withholding_statements', 'remarks')) {
                $table->string('remarks')->nullable()->after('net_income');
            }

            // 年＋ユーザーのユニーク制約がなければ付与
            // （既に別名のインデックスがあるならスキップ）
            if (!Schema::hasColumn('withholding_statements', 'year')) {
                // 念のため: もし year 自体がなければ追加
                $table->integer('year')->after('user_id');
            }
        });

        // ユニークキーは Raw で（存在チェックは SHOW INDEX で）
        try {
            \DB::statement("ALTER TABLE withholding_statements ADD UNIQUE KEY ws_user_year_unique (user_id, year)");
        } catch (\Throwable $e) {
            // 既に存在するなら無視
        }
    }

    public function down(): void
    {
        Schema::table('withholding_statements', function (Blueprint $table) {
            // 必要に応じて削除（ダウン不要なら空でOK）
            if (Schema::hasColumn('withholding_statements', 'remarks')) $table->dropColumn('remarks');
            if (Schema::hasColumn('withholding_statements', 'net_income')) $table->dropColumn('net_income');
            if (Schema::hasColumn('withholding_statements', 'social_insurance')) $table->dropColumn('social_insurance');
            if (Schema::hasColumn('withholding_statements', 'total_tax')) $table->dropColumn('total_tax');
            if (Schema::hasColumn('withholding_statements', 'total_salary')) $table->dropColumn('total_salary');
        });

        try {
            \DB::statement("ALTER TABLE withholding_statements DROP INDEX ws_user_year_unique");
        } catch (\Throwable $e) {}
    }
};

