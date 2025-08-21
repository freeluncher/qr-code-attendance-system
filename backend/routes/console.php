<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendDailyRecapToTelegram;
use App\Jobs\SendShiftReminders;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule Telegram notifications
Schedule::job(new SendShiftReminders())->everyMinute()->name('telegram-shift-reminders');

Schedule::job(new SendDailyRecapToTelegram())
    ->dailyAt('06:00')
    ->name('telegram-daily-recap')
    ->description('Send daily attendance recap to admin via Telegram');

// Alternative: Send recap for each location separately
Schedule::call(function () {
    $locations = \App\Models\Location::all();
    foreach ($locations as $location) {
        SendDailyRecapToTelegram::dispatch(now()->subDay(), $location->id);
    }
    // Send overall recap
    SendDailyRecapToTelegram::dispatch(now()->subDay(), null);
})->dailyAt('06:30')->name('telegram-location-recaps');
