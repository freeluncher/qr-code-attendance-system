<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private $botToken;
    private $apiUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send message to specific chat ID
     */
    public function sendMessage($chatId, $message, $parseMode = 'Markdown')
    {
        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
            ]);

            if ($response->successful()) {
                Log::info("Telegram message sent successfully to chat_id: {$chatId}");
                return $response->json();
            } else {
                Log::error("Failed to send Telegram message", [
                    'chat_id' => $chatId,
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Telegram API error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send attendance notification
     */
    public function sendAttendanceNotification($userId, $type, $attendance)
    {
        $user = User::find($userId);
        if (!$user || !$user->telegram_chat_id) {
            Log::warning("User not found or no telegram_chat_id set for user ID: {$userId}");
            return false;
        }

        $message = $this->buildAttendanceMessage($type, $attendance, $user);
        $result = $this->sendMessage($user->telegram_chat_id, $message);

        // Log notification
        if ($result) {
            Notification::create([
                'user_id' => $userId,
                'type' => 'telegram_' . $type,
                'message' => $message,
                'sent_at' => now(),
            ]);
        }

        return $result;
    }

    /**
     * Send shift reminder
     */
    public function sendShiftReminder($userId, $shift, $reminderType = 'upcoming')
    {
        $user = User::find($userId);
        if (!$user || !$user->telegram_chat_id) {
            return false;
        }

        $message = $this->buildShiftReminderMessage($reminderType, $shift, $user);
        $result = $this->sendMessage($user->telegram_chat_id, $message);

        if ($result) {
            Notification::create([
                'user_id' => $userId,
                'type' => 'telegram_shift_reminder',
                'message' => $message,
                'sent_at' => now(),
            ]);
        }

        return $result;
    }

    /**
     * Send admin notification (late attendance, missing attendance, etc.)
     */
    public function sendAdminNotification($adminUsers, $type, $data)
    {
        foreach ($adminUsers as $admin) {
            if (!$admin->telegram_chat_id) continue;

            $message = $this->buildAdminMessage($type, $data, $admin);
            $result = $this->sendMessage($admin->telegram_chat_id, $message);

            if ($result) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'telegram_admin_' . $type,
                    'message' => $message,
                    'sent_at' => now(),
                ]);
            }
        }
    }

    /**
     * Send severe late notification (>30 minutes) to admin
     */
    public function sendSevereLateNotification($attendance)
    {
        $lateMinutes = $this->calculateLateMinutes($attendance);

        // Only send if late > 30 minutes
        if ($lateMinutes <= 30) {
            return false;
        }

        $adminUsers = User::admins()->withTelegram()->get();
        if ($adminUsers->isEmpty()) {
            return false;
        }

        $data = [
            'user' => $attendance->user,
            'attendance' => $attendance,
            'late_minutes' => $lateMinutes,
        ];

        $this->sendAdminNotification($adminUsers, 'severe_late', $data);
        return true;
    }

    /**
     * Send daily recap to admin users
     */
    public function sendDailyRecap($date = null, $locationId = null)
    {
        $date = $date ?? now();
        $adminUsers = User::admins()->withTelegram()->get();

        if ($adminUsers->isEmpty()) {
            return false;
        }

        // Get attendance statistics for the date
        $stats = $this->getDailyAttendanceStats($date, $locationId);

        $data = [
            'date' => $date->format('d/m/Y'),
            'location' => $locationId ? \App\Models\Location::find($locationId)->name : 'Semua Lokasi',
            'stats' => $stats,
        ];

        $this->sendAdminNotification($adminUsers, 'daily_recap', $data);
        return true;
    }

    /**
     * Build attendance message
     */
    private function buildAttendanceMessage($type, $attendance, $user)
    {
        $locationName = $attendance->location ? $attendance->location->name : 'Unknown Location';
        $shiftName = $attendance->shift ? $attendance->shift->name : 'No Shift';
        $timestamp = $attendance->scanned_at ?? $attendance->check_out_time;

        switch ($type) {
            case 'clock_in':
                return "✅ *Absen Masuk Berhasil*\n\n" .
                       "👤 Nama: {$user->name}\n" .
                       "📍 Lokasi: {$locationName}\n" .
                       "🕐 Shift: {$shiftName}\n" .
                       "⏰ Waktu: " . $timestamp->format('d/m/Y H:i:s') . "\n" .
                       "📊 Status: " . $this->getAttendanceStatus($attendance);

            case 'clock_out':
                $duration = $this->calculateWorkDuration($attendance);
                return "🚪 *Absen Keluar Berhasil*\n\n" .
                       "👤 Nama: {$user->name}\n" .
                       "📍 Lokasi: {$locationName}\n" .
                       "🕐 Shift: {$shiftName}\n" .
                       "⏰ Keluar: " . $timestamp->format('d/m/Y H:i:s') . "\n" .
                       "⏱️ Durasi Kerja: {$duration}\n" .
                       "📊 Status: " . $this->getAttendanceStatus($attendance);

            case 'late_attendance':
                $lateMinutes = $this->calculateLateMinutes($attendance);
                $message = "⚠️ *Peringatan Terlambat*\n\n" .
                          "👤 Nama: {$user->name}\n" .
                          "📍 Lokasi: {$locationName}\n" .
                          "🕐 Shift: {$shiftName}\n" .
                          "⏰ Waktu Absen: " . $timestamp->format('d/m/Y H:i:s') . "\n";

                if ($lateMinutes) {
                    $message .= "⏱️ Terlambat: {$lateMinutes} menit\n";
                }

                $message .= "🔴 Status: TERLAMBAT";
                return $message;

            default:
                return "📢 Notifikasi Kehadiran untuk {$user->name}";
        }
    }

    /**
     * Build shift reminder message
     */
    private function buildShiftReminderMessage($type, $shift, $user)
    {
        $locationName = $shift->location ? $shift->location->name : 'Semua Lokasi';

        switch ($type) {
            case 'upcoming':
                return "⏰ *Pengingat Shift*\n\n" .
                       "👤 Halo {$user->name},\n" .
                       "Shift Anda akan dimulai dalam 30 menit:\n\n" .
                       "🕐 Shift: {$shift->name}\n" .
                       "📍 Lokasi: {$locationName}\n" .
                       "⏰ Waktu: {$shift->start_time} - {$shift->end_time}\n\n" .
                       "Jangan lupa untuk melakukan absen masuk! 🎯";

            case 'missed':
                return "❌ *Shift Terlewat*\n\n" .
                       "👤 {$user->name},\n" .
                       "Anda belum melakukan absen untuk shift:\n\n" .
                       "🕐 Shift: {$shift->name}\n" .
                       "📍 Lokasi: {$locationName}\n" .
                       "⏰ Waktu: {$shift->start_time} - {$shift->end_time}\n\n" .
                       "Silakan hubungi admin jika ada kendala. 📞";

            default:
                return "📢 Pengingat shift untuk {$user->name}";
        }
    }

    /**
     * Build admin notification message
     */
    private function buildAdminMessage($type, $data, $admin)
    {
        switch ($type) {
            case 'late_summary':
                $count = count($data['late_users']);
                $location = $data['location'] ?? 'Semua Lokasi';
                $userList = collect($data['late_users'])->pluck('name')->implode("\n• ");

                return "🚨 *Laporan Keterlambatan*\n\n" .
                       "📍 Lokasi: {$location}\n" .
                       "📅 Tanggal: " . now()->format('d/m/Y') . "\n" .
                       "👥 Jumlah Terlambat: {$count} orang\n\n" .
                       "Daftar yang terlambat:\n• {$userList}";

            case 'missing_attendance':
                $count = count($data['missing_users']);
                $location = $data['location'] ?? 'Semua Lokasi';
                $userList = collect($data['missing_users'])->pluck('name')->implode("\n• ");

                return "⚠️ *Absen Tidak Hadir*\n\n" .
                       "📍 Lokasi: {$location}\n" .
                       "📅 Tanggal: " . now()->format('d/m/Y') . "\n" .
                       "👥 Tidak Hadir: {$count} orang\n\n" .
                       "Daftar tidak hadir:\n• {$userList}";

            case 'shift_coverage':
                $shift = $data['shift'];
                $present = $data['present_count'];
                $required = $shift->capacity;

                return "📊 *Status Shift Coverage*\n\n" .
                       "🕐 Shift: {$shift->name}\n" .
                       "📍 Lokasi: " . ($shift->location ? $shift->location->name : 'Semua Lokasi') . "\n" .
                       "👥 Hadir: {$present}/{$required} orang\n" .
                       "📈 Coverage: " . round(($present/$required) * 100, 1) . "%";

            case 'severe_late':
                $user = $data['user'];
                $attendance = $data['attendance'];
                $lateMinutes = $data['late_minutes'];
                $locationName = $attendance->location ? $attendance->location->name : 'Unknown Location';
                $shiftName = $attendance->shift ? $attendance->shift->name : 'No Shift';

                return "🚨 *TERLAMBAT BERAT - PERLU PERHATIAN*\n\n" .
                       "👤 Nama: {$user->name}\n" .
                       "📍 Lokasi: {$locationName}\n" .
                       "🕐 Shift: {$shiftName}\n" .
                       "⏰ Waktu Absen: " . $attendance->scanned_at->format('d/m/Y H:i:s') . "\n" .
                       "⏱️ Terlambat: {$lateMinutes} menit\n" .
                       "🔴 Status: TERLAMBAT > 30 MENIT\n\n" .
                       "⚠️ Mohon segera ditindaklanjuti!";

            case 'daily_recap':
                return $this->buildDailyRecapMessage($data);

            default:
                return "📢 Notifikasi Admin";
        }
    }

    /**
     * Get attendance status text
     */
    private function getAttendanceStatus($attendance)
    {
        if ($attendance->is_late) {
            return "🔴 TERLAMBAT";
        }
        return "✅ TEPAT WAKTU";
    }

    /**
     * Calculate work duration
     */
    private function calculateWorkDuration($attendance)
    {
        if (!$attendance->scanned_at || !$attendance->check_out_time) {
            return "Belum lengkap";
        }

        $duration = $attendance->scanned_at->diff($attendance->check_out_time);
        return $duration->format('%h jam %i menit');
    }

    /**
     * Calculate late minutes for attendance
     */
    private function calculateLateMinutes($attendance)
    {
        if (!$attendance->shift || !$attendance->scanned_at) {
            return null;
        }

        $shiftStart = \Carbon\Carbon::createFromTimeString($attendance->shift->start_time);
        $clockIn = $attendance->scanned_at;

        // Handle overnight shifts
        if ($attendance->shift->isOvernightShift()) {
            // If clock in is after midnight but shift started previous day
            if ($clockIn->format('H:i') < $shiftStart->format('H:i')) {
                $shiftStart->subDay();
            }
        }

        if ($clockIn->gt($shiftStart)) {
            return $clockIn->diffInMinutes($shiftStart);
        }

        return 0;
    }

    /**
     * Build daily recap message
     */
    private function buildDailyRecapMessage($data)
    {
        $date = $data['date'] ?? now()->format('d/m/Y');
        $location = $data['location'] ?? 'Semua Lokasi';
        $stats = $data['stats'];

        $message = "📊 *REKAP HARIAN KEHADIRAN*\n\n";
        $message .= "📅 Tanggal: {$date}\n";
        $message .= "📍 Lokasi: {$location}\n\n";

        // Attendance Summary
        $message .= "👥 *RINGKASAN KEHADIRAN*\n";
        $message .= "• Total Hadir: {$stats['total_present']} orang\n";
        $message .= "• Tepat Waktu: {$stats['on_time']} orang\n";
        $message .= "• Terlambat: {$stats['late']} orang\n";
        $message .= "• Terlambat > 30 menit: {$stats['severe_late']} orang\n";
        $message .= "• Tidak Hadir: {$stats['absent']} orang\n\n";

        // Performance Indicators
        $attendanceRate = $stats['total_expected'] > 0 ? round(($stats['total_present'] / $stats['total_expected']) * 100, 1) : 0;
        $punctualityRate = $stats['total_present'] > 0 ? round(($stats['on_time'] / $stats['total_present']) * 100, 1) : 0;

        $message .= "📈 *INDIKATOR KINERJA*\n";
        $message .= "• Tingkat Kehadiran: {$attendanceRate}%\n";
        $message .= "• Tingkat Ketepatan: {$punctualityRate}%\n\n";

        // Late Employees (if any)
        if (!empty($stats['late_employees'])) {
            $message .= "🔴 *YANG TERLAMBAT*\n";
            foreach ($stats['late_employees'] as $employee) {
                $lateInfo = isset($employee['late_minutes']) ? " ({$employee['late_minutes']} menit)" : "";
                $message .= "• {$employee['name']}{$lateInfo}\n";
            }
            $message .= "\n";
        }

        // Severe Late (> 30 minutes)
        if (!empty($stats['severe_late_employees'])) {
            $message .= "🚨 *TERLAMBAT BERAT (>30 MENIT)*\n";
            foreach ($stats['severe_late_employees'] as $employee) {
                $message .= "• {$employee['name']} ({$employee['late_minutes']} menit)\n";
            }
            $message .= "\n";
        }

        // Absent Employees
        if (!empty($stats['absent_employees'])) {
            $message .= "❌ *TIDAK HADIR*\n";
            foreach ($stats['absent_employees'] as $employee) {
                $message .= "• {$employee['name']}\n";
            }
            $message .= "\n";
        }

        $message .= "📝 *Laporan dibuat otomatis oleh sistem*";

        return $message;
    }

    /**
     * Get daily attendance statistics
     */
    private function getDailyAttendanceStats($date, $locationId = null)
    {
        // Get all attendances for the date
        $attendanceQuery = \App\Models\Attendance::whereDate('scanned_at', $date)
            ->with(['user', 'shift', 'location']);

        if ($locationId) {
            $attendanceQuery->where('location_id', $locationId);
        }

        $attendances = $attendanceQuery->get();

        // Get expected users (simplified - in real app you'd have a scheduling system)
        $expectedUsersQuery = User::where('role', '!=', 'admin');
        $totalExpected = $expectedUsersQuery->count();

        // Calculate statistics
        $totalPresent = $attendances->count();
        $onTime = $attendances->where('is_late', false)->count();
        $late = $attendances->where('is_late', true)->count();

        // Calculate severe late (>30 minutes)
        $severeLate = 0;
        $severeLateEmployees = [];
        $lateEmployees = [];

        foreach ($attendances as $attendance) {
            if ($attendance->is_late) {
                $lateMinutes = $this->calculateLateMinutes($attendance);
                $employeeInfo = [
                    'name' => $attendance->user->name,
                    'late_minutes' => $lateMinutes
                ];

                $lateEmployees[] = $employeeInfo;

                if ($lateMinutes > 30) {
                    $severeLate++;
                    $severeLateEmployees[] = $employeeInfo;
                }
            }
        }

        // Get absent employees (expected but didn't clock in)
        $presentUserIds = $attendances->pluck('user_id')->toArray();
        $absentUsers = User::where('role', '!=', 'admin')
            ->whereNotIn('id', $presentUserIds)
            ->get(['id', 'name']);

        $absentEmployees = $absentUsers->map(function($user) {
            return ['name' => $user->name];
        })->toArray();

        return [
            'total_expected' => $totalExpected,
            'total_present' => $totalPresent,
            'on_time' => $onTime,
            'late' => $late,
            'severe_late' => $severeLate,
            'absent' => $absentUsers->count(),
            'late_employees' => $lateEmployees,
            'severe_late_employees' => $severeLateEmployees,
            'absent_employees' => $absentEmployees,
        ];
    }

    /**
     * Set webhook for Telegram bot
     */
    public function setWebhook($url)
    {
        try {
            $response = Http::post("{$this->apiUrl}/setWebhook", [
                'url' => $url,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error("Failed to set Telegram webhook: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get bot info
     */
    public function getBotInfo()
    {
        try {
            $response = Http::get("{$this->apiUrl}/getMe");
            return $response->json();
        } catch (\Exception $e) {
            Log::error("Failed to get Telegram bot info: " . $e->getMessage());
            return false;
        }
    }
}
