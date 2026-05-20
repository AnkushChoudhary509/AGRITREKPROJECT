<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drones table
        Schema::create('drones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('drone_id')->unique()->comment('Hardware ID');
            $table->string('model')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active','idle','offline'])->default('idle');
            $table->timestamps();
        });

        // Drone logs (telemetry)
        Schema::create('drone_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drone_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('speed', 6, 2)->default(0)->comment('km/h');
            $table->decimal('altitude', 8, 2)->default(0)->comment('meters');
            $table->decimal('direction', 6, 2)->default(0)->comment('degrees 0-360');
            $table->json('extra_data')->nullable()->comment('Any extra sensor data as JSON');
            $table->timestamps();

            $table->index(['drone_id', 'created_at']);
        });

        // Waypoints
        Schema::create('waypoints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('route_name')->nullable()->comment('Group name for waypoint routes');
            $table->foreignId('drone_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('sequence')->default(1)->comment('Order in route');
            $table->decimal('altitude', 8, 2)->default(50);
            $table->decimal('speed', 6, 2)->default(30);
            $table->text('notes')->nullable();
            $table->boolean('is_reached')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waypoints');
        Schema::dropIfExists('drone_logs');
        Schema::dropIfExists('drones');
    }
};
