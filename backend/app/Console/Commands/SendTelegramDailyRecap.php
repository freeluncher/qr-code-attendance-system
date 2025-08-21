<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendDailyRecapToTelegram;
use App\Models\Location;

class SendTelegramDailyRecap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:daily-recap
                            {--date= : Date for recap (Y-m-d format, defaults to yesterday)}
                            {--location= : Location ID (optional, sends for all locations if not specified)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily attendance recap via Telegram to admin users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateInput = $this->option('date');
        $locationId = $this->option('location');

        // Parse date
        $date = $dateInput ?
            \Carbon\Carbon::createFromFormat('Y-m-d', $dateInput) :
            now()->subDay();

        // Validate location if provided
        if ($locationId && !Location::find($locationId)) {
            $this->error("Location with ID {$locationId} not found!");
            return 1;
        }

        $this->info('Sending daily recap for ' . $date->format('Y-m-d') .
                   ($locationId ? ' (Location ID: ' . $locationId . ')' : ' (All Locations)'));

        if ($locationId) {
            // Send for specific location
            SendDailyRecapToTelegram::dispatch($date, $locationId);
            $this->info('Daily recap job dispatched for specific location!');
        } else {
            // Send for all locations + overall recap
            $locations = Location::all();

            foreach ($locations as $location) {
                SendDailyRecapToTelegram::dispatch($date, $location->id);
                $this->info("Daily recap job dispatched for {$location->name}");
            }

            // Send overall recap
            SendDailyRecapToTelegram::dispatch($date, null);
            $this->info('Overall daily recap job dispatched!');
        }

        return 0;
    }
}
