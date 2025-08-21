<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendWeeklyReportEmail;
use App\Models\Location;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Services\WeeklyReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class WeeklyReportController extends Controller
{
    protected WeeklyReportService $weeklyReportService;

    public function __construct(WeeklyReportService $weeklyReportService)
    {
        $this->weeklyReportService = $weeklyReportService;
    }

    /**
     * Get list of weekly reports
     */
    public function index(Request $request): JsonResponse
    {
        $query = WeeklyReport::with(['location', 'creator'])
            ->orderBy('week_start_date', 'desc');

        // Filter by location if provided
        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('week_start_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('week_end_date', '<=', $request->to_date);
        }

        $reports = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reports,
            'message' => 'Weekly reports retrieved successfully'
        ]);
    }

    /**
     * Generate a new weekly report
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'location_id' => 'required|exists:locations,id',
                'week_start_date' => 'required|date',
                'send_email' => 'boolean',
                'email_recipients' => 'array',
                'email_recipients.*' => 'email'
            ]);

            $location = Location::findOrFail($validated['location_id']);
            $weekStart = Carbon::parse($validated['week_start_date'])->startOfWeek();

            // Generate the report
            $report = $this->weeklyReportService->createWeeklyReport(
                $location,
                $weekStart,
                $request->user()->id
            );

            // Send emails if requested
            if ($validated['send_email'] ?? false) {
                $recipients = $validated['email_recipients'] ?? [$this->getDefaultAdminEmail()];

                foreach ($recipients as $email) {
                    if ($email) {
                        SendWeeklyReportEmail::dispatch($report, $email);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $report->load(['location', 'creator']),
                'message' => 'Weekly report generated successfully'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific weekly report
     */
    public function show(WeeklyReport $weeklyReport): JsonResponse
    {
        $weeklyReport->load(['location', 'creator']);

        return response()->json([
            'success' => true,
            'data' => $weeklyReport,
            'message' => 'Weekly report retrieved successfully'
        ]);
    }

    /**
     * Download report CSV file
     */
    public function download(WeeklyReport $weeklyReport)
    {
        if (!$weeklyReport->file_path || !Storage::exists($weeklyReport->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Report file not found'
            ], 404);
        }

        $filename = basename($weeklyReport->file_path);

        return Storage::download($weeklyReport->file_path, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Send report via email
     */
    public function sendEmail(Request $request, WeeklyReport $weeklyReport): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email_recipients' => 'required|array|min:1',
                'email_recipients.*' => 'email'
            ]);

            foreach ($validated['email_recipients'] as $email) {
                SendWeeklyReportEmail::dispatch($weeklyReport, $email);
            }

            return response()->json([
                'success' => true,
                'message' => 'Report emails queued successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send emails: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete weekly report
     */
    public function destroy(WeeklyReport $weeklyReport): JsonResponse
    {
        try {
            // Delete the file if it exists
            if ($weeklyReport->file_path && Storage::exists($weeklyReport->file_path)) {
                Storage::delete($weeklyReport->file_path);
            }

            $weeklyReport->delete();

            return response()->json([
                'success' => true,
                'message' => 'Weekly report deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available locations for report generation
     */
    public function locations(): JsonResponse
    {
        $locations = $this->weeklyReportService->getAllActiveLocations();

        return response()->json([
            'success' => true,
            'data' => $locations,
            'message' => 'Active locations retrieved successfully'
        ]);
    }

    /**
     * Preview report data without saving
     */
    public function preview(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'location_id' => 'required|exists:locations,id',
                'week_start_date' => 'required|date'
            ]);

            $location = Location::findOrFail($validated['location_id']);
            $weekStart = Carbon::parse($validated['week_start_date'])->startOfWeek();

            $reportData = $this->weeklyReportService->generateWeeklyReportData($location, $weekStart);

            return response()->json([
                'success' => true,
                'data' => $reportData,
                'message' => 'Report preview generated successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get default admin email
     */
    private function getDefaultAdminEmail(): ?string
    {
        if ($adminEmail = config('mail.admin_email')) {
            return $adminEmail;
        }

        $adminUser = User::where('role', 'admin')->first();
        return $adminUser?->email;
    }
}
