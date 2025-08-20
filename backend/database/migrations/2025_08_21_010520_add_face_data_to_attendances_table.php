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
            $table->string('face_photo_url')->nullable()->after('photo_url');
            $table->json('face_landmarks')->nullable()->after('face_photo_url');
            $table->json('face_descriptor')->nullable()->after('face_landmarks');
            $table->string('face_quality_status')->nullable()->after('face_descriptor');
            $table->text('face_validation_message')->nullable()->after('face_quality_status');
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
                'face_validation_message'
            ]);
        });
    }
};
