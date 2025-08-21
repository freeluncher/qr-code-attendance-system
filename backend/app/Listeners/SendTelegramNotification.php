<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use App\Services\TelegramService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTelegramNotification implements ShouldQueue
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the event.
     */
    public function handle(AttendanceRecorded $event): void
    {
        $attendance = $event->attendance;
        $user = $attendance->user;

        // Send notification to user if they have Telegram enabled
        if ($user->hasTelegramEnabled()) {
            $this->sendUserNotification($attendance, $event->type);
        }

        // Send admin notifications for certain events
        if (in_array($event->type, ['late_attendance', 'no_show'])) {
            $this->sendAdminNotifications($attendance, $event->type);
        }

        // Send severe late notification to admin (>30 minutes)
        if ($event->type === 'late_attendance') {
            $this->telegramService->sendSevereLateNotification($attendance);
        }
    }

    /**
     * Send notification to user
     */
    private function sendUserNotification($attendance, $type)
    {
        $this->telegramService->sendAttendanceNotification(
            $attendance->user_id,
            $type,
            $attendance
        );
    }

    /**
     * Send notifications to admin users
     */
    private function sendAdminNotifications($attendance, $type)
    {
        $adminUsers = User::admins()->withTelegram()->get();

        if ($adminUsers->isEmpty()) {
            return;
        }

        $data = [
            'user' => $attendance->user,
            'attendance' => $attendance,
            'location' => $attendance->location,
            'shift' => $attendance->shift,
        ];

        $this->telegramService->sendAdminNotification($adminUsers, $type, $data);
    }
}
