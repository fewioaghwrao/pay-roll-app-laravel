<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
Schema::create('payslips', function (Blueprint $table) {
$table->id();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->year('year');
$table->unsignedTinyInteger('month'); // 1-12
$table->enum('pay_type', ['salary','bonus']); // 給与 / 賞与
$table->integer('gross_amount');
$table->integer('tax_amount')->default(0);
$table->integer('net_amount');
$table->json('items')->nullable(); // 明細項目
$table->timestamps(); // updated_at で降順
$table->unique(['user_id','year','month','pay_type']);
});
}
public function down(): void { Schema::dropIfExists('payslips'); }
};