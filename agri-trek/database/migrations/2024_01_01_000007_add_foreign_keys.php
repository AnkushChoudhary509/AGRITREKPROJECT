<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This migration runs LAST to add all cross-table foreign keys
 * after every table has been created.
 */
return new class extends Migration {
    public function up(): void
    {
        // users.farmer_id -> farmers.id  (added after farmers table exists)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('farmer_id')
                  ->references('id')
                  ->on('farmers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['farmer_id']);
        });
    }
};
