<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SatpamController;

// Authentication Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Protected Routes only for Admin
Route::middleware(['auth:sanctum', 'checkUserRole:admin'])->group(function () {

    //User CRUD
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{id}', [UserController::class, 'update']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Location CRUD
    Route::post('/geocode/address', [LocationController::class, 'geocode']);
    Route::resource('/locations', LocationController::class)->except(['create', 'edit']);

    //Shift CRUD
    Route::resource('/shifts', ShiftController::class)->except(['create', 'edit']);

    // QrCode CRUD
    Route::resource('/qrcodes', QrCodeController::class)->except(['create', 'edit']);
    Route::post('/qrcodes/{id}/renew', [QrCodeController::class, 'renew']);
    Route::get('/qrcodes/{id}/image', [QrCodeController::class, 'generateImage']);

    // Attendance (Presensi/Absensi)
    Route::resource('/attendances', AttendanceController::class)->except(['create', 'edit', 'update', 'destroy']);

    // Dashboard Statistics for Admin
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'adminStats']);
        Route::get('/activities', [DashboardController::class, 'recentActivities']);
        Route::get('/late-employees', [DashboardController::class, 'topLateEmployees']);
        Route::get('/attendance-chart', [DashboardController::class, 'attendanceChart']);
    });

});

// Routes for Satpam
Route::middleware(['auth:sanctum', 'checkUserRole:satpam'])->group(function () {
    // Presensi
    Route::post('/attendances', [AttendanceController::class, 'store']);

    // Dashboard Statistics for Satpam
    Route::prefix('dashboard')->group(function () {
        Route::get('/my-stats', [DashboardController::class, 'satpamStats']);
        Route::get('/my-history', [DashboardController::class, 'satpamHistory']);
    });

    // Satpam specific routes
    Route::prefix('satpam')->group(function () {
        // Dashboard
        Route::get('/stats', [SatpamController::class, 'getDashboardStats']);
        Route::get('/today-attendance', [SatpamController::class, 'getTodayAttendance']);
        Route::get('/monthly-stats', [SatpamController::class, 'getMonthlyStats']);
        Route::get('/today-schedule', [SatpamController::class, 'getTodaySchedule']);
        Route::get('/recent-activities', [SatpamController::class, 'getRecentActivities']);

        // Attendance/QR Scanning
        // QR Attendance
    Route::post('/qr-attendance', [SatpamController::class, 'processQrAttendance']);
    
    // Face Recognition
    Route::post('/register-face', [SatpamController::class, 'registerFaceReference']);
    Route::get('/check-face-registration', [SatpamController::class, 'checkFaceRegistration']);

        // History
        Route::get('/attendance-history', [SatpamController::class, 'getAttendanceHistory']);

        // Schedule
        Route::get('/schedule', [SatpamController::class, 'getSchedule']);
    });
});
