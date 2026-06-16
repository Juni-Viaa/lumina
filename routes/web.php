<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── Route untuk semua user yang sudah login ──────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard / Chat
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::post('/ask', [DashboardController::class, 'ask'])->name('dashboard.ask');
    Route::get('/history/{queryLog}', [DashboardController::class, 'show'])->name('dashboard.show');
    Route::get('/dashboard/history', [DashboardController::class, 'historyJson'])->name('dashboard.history-json');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Change Password
    Route::get('/change-password', [ChangePasswordController::class, 'editPassword'])->name('change-password.index');
    Route::patch('/change-password', [ChangePasswordController::class, 'editPassword'])->name('profile.password');
    Route::post('/change-password', [ChangePasswordController::class, 'updatePassword'])->name('profile.password.update');

    // History
    Route::get('/history', [DashboardController::class, 'history'])->name('history.index');
});


// ── Route khusus Admin ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/upload', [UploadController::class, 'index'])->name('uploads.index');

    Route::get('/upload/list', [UploadController::class, 'list'])->name('uploads.list');

    Route::post('/upload', [UploadController::class, 'store'])->name('uploads.store');

    Route::delete('/upload/{upload}', [UploadController::class, 'destroy'])->name('uploads.destroy');
});

require __DIR__.'/auth.php';