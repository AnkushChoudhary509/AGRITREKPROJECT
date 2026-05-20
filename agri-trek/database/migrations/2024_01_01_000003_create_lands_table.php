<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->cascadeOnDelete();
            $table->decimal('area', 8, 2)->comment('Area in acres');
            $table->enum('soil_type', ['Clay','Sandy','Loamy','Silty','Peaty','Chalky','Black Cotton'])->default('Loamy');
            $table->string('crop_type');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('irrigation_type', ['Canal','Drip','Sprinkler','Rainfed','Borewell','Pond'])->default('Rainfed');
            $table->string('survey_number')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lands'); }
};
