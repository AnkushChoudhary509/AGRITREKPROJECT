<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vision_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('mode');
            $table->integer('object_count')->default(0);
            $table->integer('healthy_pct')->default(0);
            $table->integer('affected_pct')->default(0);
            $table->json('result_json')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('vision_analyses'); }
};
