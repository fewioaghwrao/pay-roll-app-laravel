<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
Schema::table('users', function (Blueprint $table) {
$table->string('employment_type')->default('正社員'); // 正社員 / 契約社員
});
}
public function down(): void {
Schema::table('users', function (Blueprint $table) {
$table->dropColumn('employment_type');
});
}
};
