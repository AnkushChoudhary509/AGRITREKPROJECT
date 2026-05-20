<?php
// ============================================================
// 2024_01_01_000002_create_farmers_table.php
// ============================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 10)->unique();
            $table->text('address')->nullable();
            $table->string('village');
            $table->string('district')->nullable();
            $table->string('aadhaar', 12)->nullable()->unique();
            $table->date('dob')->nullable();
            $table->string('bank_account', 20)->nullable();
            $table->string('ifsc_code', 15)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('farmers'); }
};
