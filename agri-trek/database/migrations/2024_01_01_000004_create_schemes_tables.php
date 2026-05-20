<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Schemes table
        Schema::create('schemes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('eligibility')->nullable();
            $table->decimal('subsidy_amount', 12, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('department')->nullable();
            $table->timestamps();
        });

        // Scheme applications table
        Schema::create('scheme_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scheme_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->date('applied_date')->default(now());
            $table->date('approved_date')->nullable();
            $table->timestamps();
            $table->unique(['farmer_id', 'scheme_id']); // one application per scheme
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('scheme_applications');
        Schema::dropIfExists('schemes');
    }
};
