<?php

namespace App\Console\Commands;

use App\Mail\WeeklyReportMail;
use App\Models\Location;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Services\WeeklyReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateWeeklyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:weekly
                           {--location= : Specific location ID to generate report for}
                           {--week= : Specific week start date (Y-m-d format), default is last week}
                           {--send-email : Send report via email to admins}
                           {--email= : Specific email address to send to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate weekly attendance reports for all or specific locations';

    protected WeeklyReportService $weeklyReportService;

    public function __construct(WeeklyReportService $weeklyReportService)
    {
        parent::__construct();
        $this->weeklyReportService = $weeklyReportService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting weekly report generation...');

        // Determine week start date
        $weekStartDate = $this->option('week')
            ? Carbon::parse($this->option('week'))->startOfWeek()
            : Carbon::now()->subWeek()->startOfWeek();

        $this->info("📅 Generating reports for week: {$weekStartDate->format('d M Y')} - {$weekStartDate->copy()->endOfWeek()->format('d M Y')}");

        // Determine locations to process
        $locations = $this->getLocationsToProcess();

        if ($locations->isEmpty()) {
            $this->error('❌ No active locations found to generate reports for.');
            return self::FAILURE;
        }

        $this->info("📍 Processing {$locations->count()} location(s)...");

        $successCount = 0;
        $errorCount = 0;

        foreach ($locations as $location) {
            try {
                $this->info("Processing: {$location->name}");

                // Generate report
                $report = $this->weeklyReportService->createWeeklyReport($location, $weekStartDate);

                $this->info("✅ Report generated for {$location->name}");
                $this->line("   📄 File: {$report->file_path}");

                // Send email if requested
                if ($this->option('send-email')) {
                    $this->sendReportEmail($report);
                }

                $successCount++;

            } catch (\Exception $e) {
                $this->error("❌ Error generating report for {$location->name}: " . $e->getMessage());
                Log::error("Weekly report generation failed for location {$location->id}", [
                    'location' => $location->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $errorCount++;
            }
        }

        // Summary
        $this->newLine();
        $this->info("📊 Generation Summary:");
        $this->info("   ✅ Successful: {$successCount}");
        if ($errorCount > 0) {
            $this->error("   ❌ Failed: {$errorCount}");
        }

        return $successCount > 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Get locations to process based on options
     */
    private function getLocationsToProcess()
    {
        if ($locationId = $this->option('location')) {
            return Location::where('id', $locationId)->get(); // Remove is_active filter
        }

        return $this->weeklyReportService->getAllActiveLocations();
    }

    /**
     * Send report via email
     */
    private function sendReportEmail(WeeklyReport $report): void
    {
        try {
            // Get recipient email
            $recipientEmail = $this->option('email') ?: $this->getDefaultAdminEmail();

            if (!$recipientEmail) {
                $this->warn("⚠️  No email address configured. Skipping email for {$report->location->name}");
                return;
            }

            // Send email
            Mail::to($recipientEmail)->send(new WeeklyReportMail($report));

            // Update report as sent
            $report->update(['email_sent_at' => now()]);

            $this->info("   📧 Email sent to: {$recipientEmail}");

        } catch (\Exception $e) {
            $this->error("   ❌ Email failed: " . $e->getMessage());
            Log::error("Weekly report email failed", [
                'report_id' => $report->id,
                'location' => $report->location->name,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get default admin email from environment or database
     */
    private function getDefaultAdminEmail(): ?string
    {
        // Try to get from environment
        if ($adminEmail = config('mail.admin_email')) {
            return $adminEmail;
        }

        // Fallback to first admin user
        $adminUser = User::where('role', 'admin')->first();
        return $adminUser?->email;
    }
}
