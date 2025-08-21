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
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->json('report_data'); // Store attendance data for the week
            $table->string('file_path')->nullable(); // Path to exported file
            $table->timestamp('email_sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Ensure unique weekly reports per location
            $table->unique(['location_id', 'week_start_date'], 'unique_location_week');

            // Indexes for performance
            $table->index(['week_start_date', 'week_end_date']);
            $table->index('email_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
