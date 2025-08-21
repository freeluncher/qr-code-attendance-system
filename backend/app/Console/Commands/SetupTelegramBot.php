<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;

class SetupTelegramBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:setup {webhook_url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Telegram bot webhook and configuration';

    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up Telegram Bot...');

        // Get bot info
        $botInfo = $this->telegramService->getBotInfo();

        if (!$botInfo) {
            $this->error('Failed to get bot information. Please check your TELEGRAM_BOT_TOKEN.');
            return;
        }

        $this->info("Bot found: {$botInfo['result']['first_name']} (@{$botInfo['result']['username']})");

        // Setup webhook if URL provided
        $webhookUrl = $this->argument('webhook_url') ?? $this->ask('Enter webhook URL (optional)');

        if ($webhookUrl) {
            $this->info('Setting webhook...');
            $result = $this->telegramService->setWebhook($webhookUrl);

            if ($result && $result['ok']) {
                $this->info("Webhook set successfully to: {$webhookUrl}");
            } else {
                $this->error('Failed to set webhook');
                $this->line('Error: ' . json_encode($result));
            }
        }

        $this->info('Telegram bot setup completed!');
        $this->line('');
        $this->info('Next steps:');
        $this->line('1. Make sure users register their Telegram accounts using: /register [username]');
        $this->line('2. Set up scheduled tasks for shift reminders');
        $this->line('3. Configure environment variables:');
        $this->line('   - TELEGRAM_BOT_TOKEN=<your_bot_token>');
        $this->line('   - TELEGRAM_WEBHOOK_URL=http://yourdomain.com/api/telegram/webhook');
    }
}
