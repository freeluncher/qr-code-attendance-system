<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Location;
use App\Models\Shift;
use App\Models\QrCode;
use App\Models\Attendance;
use App\Models\AiPrediction;
use App\Models\Notification;

class VerifyDatabaseSetup extends Command
{
    protected $signature = 'db:verify-setup';
    protected $description = 'Verify that database has been properly seeded with CSV data';

    public function handle()
    {
        $this->info('🔍 Verifying Database Setup...');
        $this->line('');

        // Count records
        $counts = [
            'Users' => User::count(),
            'Locations' => Location::count(),
            'Shifts' => Shift::count(),
            'QR Codes' => QrCode::count(),
            'Attendances' => Attendance::count(),
            'AI Predictions' => AiPrediction::count(),
            'Notifications' => Notification::count(),
        ];

        // Expected counts
        $expected = [
            'Users' => 8,
            'Locations' => 7,
            'Shifts' => 5,
            'QR Codes' => 15,
            'Attendances' => 15,
            'AI Predictions' => 10,
            'Notifications' => 6,
        ];

        // Display counts
        $this->info('📊 Record Counts:');
        $allGood = true;
        foreach ($counts as $model => $count) {
            $status = $count === $expected[$model] ? '✅' : '❌';
            $this->line("  {$status} {$model}: {$count} (expected: {$expected[$model]})");
            if ($count !== $expected[$model]) {
                $allGood = false;
            }
        }

        $this->line('');

        // Verify key data
        $this->info('🔐 Key Data Verification:');

        $admin = User::where('role', 'admin')->first();
        $this->line('  ✅ Admin User: ' . ($admin ? $admin->email : 'NOT FOUND'));

        $satpamCount = User::where('role', 'satpam')->count();
        $this->line('  ✅ Satpam Users: ' . $satpamCount);

        $activeLocations = Location::where('status', 'aktif')->count();
        $this->line('  ✅ Active Locations: ' . $activeLocations);

        $latestAttendance = Attendance::latest('scanned_at')->first();
        $this->line('  ✅ Latest Attendance: ' . ($latestAttendance ? $latestAttendance->scanned_at : 'NONE'));

        $highRiskPredictions = AiPrediction::where('risk_score', '>', 0.7)->count();
        $this->line('  ✅ High Risk Predictions: ' . $highRiskPredictions);

        $this->line('');

        // Verify relationships
        $this->info('🔗 Relationship Verification:');

        $attendanceWithValidRefs = Attendance::whereHas('user')
            ->whereHas('location')
            ->whereHas('shift')
            ->whereHas('qrCode')
            ->count();
        $this->line('  ✅ Attendances with valid references: ' . $attendanceWithValidRefs . '/' . $counts['Attendances']);

        $qrCodesWithValidRefs = QrCode::whereHas('location')
            ->whereHas('shift')
            ->count();
        $this->line('  ✅ QR Codes with valid references: ' . $qrCodesWithValidRefs . '/' . $counts['QR Codes']);

        $predictionsWithValidRefs = AiPrediction::whereHas('user')
            ->whereHas('location')
            ->count();
        $this->line('  ✅ AI Predictions with valid references: ' . $predictionsWithValidRefs . '/' . $counts['AI Predictions']);

        $this->line('');

        // Final status
        if ($allGood && $attendanceWithValidRefs === $counts['Attendances'] &&
            $qrCodesWithValidRefs === $counts['QR Codes'] &&
            $predictionsWithValidRefs === $counts['AI Predictions']) {
            $this->info('🎉 Database setup is PERFECT! All data seeded correctly.');
            $this->info('📱 Ready for functional testing!');
            $this->line('');
            $this->info('🚀 Test accounts:');
            $this->line('   Admin: admin@admin.com / password');
            $this->line('   Satpam: john@satpam.com / password (and 6 others)');
        } else {
            $this->error('❌ Database setup has issues. Please check the seeding process.');
            return 1;
        }

        return 0;
    }
}
