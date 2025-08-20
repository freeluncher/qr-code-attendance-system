<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LocationSeeder::class,
            ShiftSeeder::class,
            QrCodeSeeder::class,
            AttendanceSeeder::class,
            AttendanceAuditSeeder::class,
            NotificationSeeder::class,
            ReportSeeder::class,
            AiPredictionSeeder::class,
        ]);
    }
}
