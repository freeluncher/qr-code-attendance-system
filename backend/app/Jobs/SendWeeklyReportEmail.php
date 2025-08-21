<?php

namespace App\Jobs;

use App\Mail\WeeklyReportMail;
use App\Models\WeeklyReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyReportEmail implements ShouldQueue
{
    use Queueable;

    public WeeklyReport $report;
    public string $recipientEmail;

    /**
     * Create a new job instance.
     */
    public function __construct(WeeklyReport $report, string $recipientEmail)
    {
        $this->report = $report;
        $this->recipientEmail = $recipientEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Sending weekly report email", [
                'report_id' => $this->report->id,
                'location' => $this->report->location->name,
                'recipient' => $this->recipientEmail,
                'week_start' => $this->report->week_start_date->format('Y-m-d')
            ]);

            // Send the email
            Mail::to($this->recipientEmail)->send(new WeeklyReportMail($this->report));

            // Update the report as sent
            $this->report->update(['email_sent_at' => now()]);

            Log::info("Weekly report email sent successfully", [
                'report_id' => $this->report->id,
                'recipient' => $this->recipientEmail
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send weekly report email", [
                'report_id' => $this->report->id,
                'location' => $this->report->location->name,
                'recipient' => $this->recipientEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Weekly report email job failed permanently", [
            'report_id' => $this->report->id,
            'location' => $this->report->location->name,
            'recipient' => $this->recipientEmail,
            'error' => $exception->getMessage()
        ]);
    }
}
