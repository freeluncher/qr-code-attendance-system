<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckReportFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check report files in storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = Storage::disk('local')->files('reports/weekly');

        $this->info("Files in reports/weekly:");
        foreach ($files as $file) {
            $this->line("- " . $file);
            $this->line("  Size: " . Storage::disk('local')->size($file) . " bytes");
        }

        if (empty($files)) {
            $this->warn("No files found in reports/weekly");
        }

        // Show content of first file
        if (!empty($files)) {
            $this->info("\nContent of " . $files[0] . ":");
            $content = Storage::disk('local')->get($files[0]);
            $this->line($content);
        }
    }
}
