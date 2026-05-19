<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ChangePasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return auth()->check()
        ? redirect()->route('dashboard.index')
        : redirect()->route('login');

});

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard.index');


    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    */

    Route::get('/uploads', [UploadController::class, 'index'])
        ->name('uploads.index');

    Route::post('/uploads', [UploadController::class, 'store'])
        ->name('uploads.store');

    Route::delete('/uploads/{upload}', [UploadController::class, 'destroy'])
        ->name('uploads.destroy');


    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    */

    Route::get('/history', [HistoryController::class, 'index'])
        ->name('history.index');


    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */

    Route::get('/change-password', [ChangePasswordController::class, 'editPassword'])
        ->name('profile.password');

    Route::post('/change-password', [ChangePasswordController::class, 'updatePassword'])
        ->name('profile.password.update');

});

require __DIR__.'/auth.php';