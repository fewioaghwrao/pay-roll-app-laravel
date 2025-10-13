<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
Schema::create('withholding_statements', function (Blueprint $table) {
$table->id();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->year('year'); // 対象年度
$table->integer('total_paid')->default(0);
$table->integer('total_tax')->default(0);
$table->json('meta')->nullable();
$table->timestamps();
$table->unique(['user_id','year']);
});
}
public function down(): void { Schema::dropIfExists('withholding_statements'); }
};