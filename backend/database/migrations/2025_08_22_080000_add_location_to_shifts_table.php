<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            // Add location relationship to shifts
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('cascade');

            // Add day-specific availability
            $table->json('active_days')->nullable(); // [1,2,3,4,5] for Mon-Fri

            // Add shift capacity
            $table->integer('capacity')->default(1); // How many satpam can work this shift

            // Add status
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Add description
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn(['location_id', 'active_days', 'capacity', 'status', 'description']);
        });
    }
};
