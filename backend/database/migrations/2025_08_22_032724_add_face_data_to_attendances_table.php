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
        Schema::table('attendances', function (Blueprint $table) {
            // Add face photo URL
            $table->string('face_photo_url')->nullable();

            // Add face landmarks as JSON
            $table->json('face_landmarks')->nullable();

            // Add face descriptor as JSON
            $table->json('face_descriptor')->nullable();

            // Add face quality status
            $table->string('face_quality_status')->nullable();

            // Add face validation message
            $table->text('face_validation_message')->nullable();

            // Add check out time and location
            $table->dateTime('check_out_time')->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();

            // Add notes
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'face_photo_url',
                'face_landmarks',
                'face_descriptor',
                'face_quality_status',
                'face_validation_message',
                'check_out_time',
                'check_out_latitude',
                'check_out_longitude',
                'notes'
            ]);
        });
    }
};
