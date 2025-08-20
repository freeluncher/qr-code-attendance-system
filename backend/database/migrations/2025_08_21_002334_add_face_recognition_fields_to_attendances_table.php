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
            $table->json('face_landmarks')->nullable()->after('photo_url');
            $table->boolean('face_verified')->default(false)->after('face_landmarks');
            $table->float('face_confidence')->nullable()->after('face_verified');
            $table->text('late_reason')->nullable()->after('face_confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'face_landmarks',
                'face_verified',
                'face_confidence',
                'late_reason'
            ]);
        });
    }
};
