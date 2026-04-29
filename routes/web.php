<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ChangePasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return view('dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
Route::delete('/uploads/{upload}', [UploadController::class, 'destroy'])->name('uploads.destroy');

Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

Route::get('/change-password', [ChangePasswordController::class, 'index'])->name('change-password.index');
Route::get('/change-password', [ChangePasswordController::class, 'editPassword'])->name('profile.password');
Route::post('/change-password', [ChangePasswordController::class, 'updatePassword'])->name('profile.password.update');

require __DIR__.'/auth.php';