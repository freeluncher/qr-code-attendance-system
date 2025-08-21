<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Http\Request;

class TestWebhookProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-webhook {chat_id} {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test webhook processing with simulated Telegram message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chatId = $this->argument('chat_id');
        $message = $this->argument('message');

        $this->info("Testing webhook processing for chat ID: {$chatId} with message: {$message}");

        // Simulate webhook data
        $webhookData = [
            'message' => [
                'message_id' => 123,
                'chat' => [
                    'id' => (int)$chatId,
                    'type' => 'private'
                ],
                'from' => [
                    'id' => (int)$chatId,
                    'is_bot' => false,
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'username' => 'testuser'
                ],
                'date' => time(),
                'text' => $message
            ]
        ];

        // Create request
        $request = Request::create('/api/telegram/webhook', 'POST', $webhookData, [], [], [], json_encode($webhookData));
        $request->headers->set('Content-Type', 'application/json');

        // Process webhook
        $controller = new TelegramWebhookController(app(\App\Services\TelegramService::class));
        $response = $controller->handle($request);

        $this->info("Webhook processed successfully!");
        $this->info("Response: " . $response->getContent());
    }
}
