<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDailyRecapToTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;
    protected $locationId;

    /**
     * Create a new job instance.
     */
    public function __construct($date = null, $locationId = null)
    {
        $this->date = $date ?? now()->subDay(); // Default to yesterday
        $this->locationId = $locationId;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        try {
            Log::info('Sending daily recap via Telegram', [
                'date' => $this->date->format('Y-m-d'),
                'location_id' => $this->locationId
            ]);

            $result = $telegramService->sendDailyRecap($this->date, $this->locationId);

            if ($result) {
                Log::info('Daily recap sent successfully via Telegram');
            } else {
                Log::warning('Failed to send daily recap - no admin users with Telegram');
            }
        } catch (\Exception $e) {
            Log::error('Error sending daily recap via Telegram: ' . $e->getMessage());
            throw $e;
        }
    }
}
