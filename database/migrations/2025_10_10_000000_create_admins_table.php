<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
            return;
        }

        // 既存テーブルがある場合は、足りないカラムだけ追加（任意）
        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'remember_token')) {
                $table->rememberToken();
            }
            if (!Schema::hasColumn('admins', 'created_at')) {
                $table->timestamps();
            }
            // 必要に応じて他の不足カラムも追加
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
