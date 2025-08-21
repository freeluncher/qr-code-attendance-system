<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendShiftReminders;

class SendTelegramShiftReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:shift-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Telegram notifications for upcoming shifts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending shift reminders...');

        SendShiftReminders::dispatch();

        $this->info('Shift reminders job dispatched successfully!');
    }
}
