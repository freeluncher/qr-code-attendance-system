<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

$today = Carbon::today();

$totalAttendances = Attendance::whereDate('scanned_at', $today)->count();
$uniqueUsers = Attendance::whereDate('scanned_at', $today)->distinct('user_id')->count('user_id');
$totalSatpam = User::where('role', 'satpam')->count();

echo "Total attendances today: " . $totalAttendances . "\n";
echo "Unique users attended today: " . $uniqueUsers . "\n";
echo "Total satpam users: " . $totalSatpam . "\n";
echo "Current calculation (wrong): " . round(($totalAttendances / $totalSatpam) * 100, 1) . "%\n";
echo "Correct calculation: " . round(($uniqueUsers / $totalSatpam) * 100, 1) . "%\n";

// Show attendance records for today
echo "\nToday's attendance records:\n";
$attendances = Attendance::with('user')
    ->whereDate('scanned_at', $today)
    ->get();

foreach ($attendances as $attendance) {
    echo "- User: " . $attendance->user->name . " (ID: " . $attendance->user_id . ") at " . $attendance->scanned_at . "\n";
}
