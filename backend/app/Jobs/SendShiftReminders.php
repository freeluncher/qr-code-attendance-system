<?php

namespace App\Jobs;

use App\Services\TelegramService;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendShiftReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $telegramService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->telegramService = app(TelegramService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $currentTime = now();
        $reminderTime = $currentTime->addMinutes(30); // 30 minutes ahead
        $dayOfWeek = $currentTime->dayOfWeekIso; // 1=Monday, 7=Sunday

        // Get shifts that start in 30 minutes
        $upcomingShifts = Shift::active()
            ->where(function($query) use ($reminderTime) {
                $query->whereTime('start_time', '=', $reminderTime->format('H:i'));
            })
            ->where(function($query) use ($dayOfWeek) {
                $query->whereNull('active_days')
                      ->orWhereJsonContains('active_days', $dayOfWeek);
            })
            ->with('location')
            ->get();

        foreach ($upcomingShifts as $shift) {
            $this->sendShiftReminders($shift);
        }
    }

    /**
     * Send shift reminders to assigned users
     */
    private function sendShiftReminders($shift)
    {
        // In a real implementation, you would have a way to determine
        // which users are assigned to which shifts. For now, we'll send
        // to all active users who have Telegram enabled.
        // You might have a pivot table or assignment system.

        $users = User::withTelegram()
            ->where('role', '!=', 'admin')
            ->get();

        foreach ($users as $user) {
            $this->telegramService->sendShiftReminder(
                $user->id,
                $shift,
                'upcoming'
            );
        }
    }
}
