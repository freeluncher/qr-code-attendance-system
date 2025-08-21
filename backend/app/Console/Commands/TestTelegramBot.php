<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class TestTelegramBot extends Command
{
    protected $signature = 'telegram:test {chat_id} {message?}';
    protected $description = 'Test Telegram bot by sending a message';

    public function handle(TelegramService $telegramService)
    {
        $chatId = $this->argument('chat_id');
        $message = $this->argument('message') ?? 'Test message from QR Attendance System Bot! 🤖';

        $this->info("Sending test message to chat ID: {$chatId}");

        $result = $telegramService->sendMessage($chatId, $message);

        if ($result) {
            $this->info("✅ Message sent successfully!");
            $this->info("Response: " . json_encode($result, JSON_PRETTY_PRINT));
        } else {
            $this->error("❌ Failed to send message!");
            $this->error("Check the logs for more details.");
        }
    }
}
