<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetupTelegramWebhookWithHeaders extends Command
{
    protected $signature = 'telegram:setup-webhook-headers {url}';
    protected $description = 'Setup Telegram webhook with custom headers for ngrok';

    public function handle()
    {
        $webhookUrl = $this->argument('url');
        $botToken = config('services.telegram.bot_token');

        $this->info("Setting up Telegram webhook with bypass headers...");

        // Unfortunately, Telegram doesn't support custom headers in webhook requests
        // So we need to use a different approach - middleware to bypass ngrok warning

        $this->warn("⚠️  Telegram API doesn't support custom headers for webhooks.");
        $this->info("✅ Alternative solutions:");
        $this->line("1. Use polling (currently working): php artisan telegram:polling");
        $this->line("2. Use ngrok with authentication: ngrok http 8000 --basic-auth='username:password'");
        $this->line("3. Upgrade to ngrok Pro to remove warning page");
        $this->line("4. Use alternative tunneling service like localtunnel");

        return 0;
    }
}
