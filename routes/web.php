<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\KasbonController;
use App\Http\Controllers\PayrollController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard - role-based redirect
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Attendance - clock in/out (all roles)
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        // Employee CRUD
        Route::resource('employees', EmployeeController::class);

        // Attendances management
        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');

        // Overtime management
        Route::get('/overtimes', [OvertimeController::class, 'index'])->name('overtimes.index');
        Route::get('/overtimes/create', [OvertimeController::class, 'create'])->name('overtimes.create');
        Route::post('/overtimes', [OvertimeController::class, 'store'])->name('overtimes.store');
        Route::delete('/overtimes/{overtime}', [OvertimeController::class, 'destroy'])->name('overtimes.destroy');

        // Kasbon management
        Route::get('/kasbons', [KasbonController::class, 'index'])->name('kasbons.index');
        Route::get('/kasbons/create', [KasbonController::class, 'create'])->name('kasbons.create');
        Route::post('/kasbons', [KasbonController::class, 'store'])->name('kasbons.store');
        Route::delete('/kasbons/{kasbon}', [KasbonController::class, 'destroy'])->name('kasbons.destroy');

        // Payroll management
        Route::get('/payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
        Route::get('/payrolls/{payroll}', [PayrollController::class, 'show'])->name('payrolls.show');
        Route::post('/payrolls/generate-weekly', [PayrollController::class, 'generateWeekly'])->name('payrolls.generateWeekly');
        Route::post('/payrolls/generate-monthly', [PayrollController::class, 'generateMonthly'])->name('payrolls.generateMonthly');
        Route::patch('/payrolls/{payroll}/mark-paid', [PayrollController::class, 'markPaid'])->name('payrolls.markPaid');
    });

    // Supervisor routes - can view attendances
    Route::middleware('role:admin,supervisor')->group(function () {
        Route::get('/supervisor/attendances', [AttendanceController::class, 'index'])->name('supervisor.attendances');
    });
});

require __DIR__.'/auth.php';
