<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\AttendanceController;

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
    Route::resource('/locations', LocationController::class)->except(['create', 'edit']);

    //Shift CRUD
    Route::resource('/shifts', ShiftController::class)->except(['create', 'edit']);

    // QrCode CRUD
    Route::resource('/qrcodes', QrCodeController::class)->except(['create', 'edit']);

    // Attendance (Presensi/Absensi)
    Route::resource('/attendances', AttendanceController::class)->except(['create', 'edit', 'update', 'destroy']);

    });

    // Route presensi untuk satpam
    Route::middleware(['auth:sanctum', 'checkUserRole:satpam'])->group(function () {
        Route::post('/attendances', [App\Http\Controllers\AttendanceController::class, 'store']);
    });
    // Jika ingin satpam bisa presensi, bisa buka akses POST attendances untuk role satpam di group middleware lain.
