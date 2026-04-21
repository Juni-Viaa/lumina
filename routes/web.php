<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\HistoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard Chat


    // Documents
    

});

Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
Route::delete('/uploads/{upload}', [UploadController::class, 'destroy'])->name('uploads.destroy');

Route::get('/history', [HistoryController::class, 'index'])->name('history.index');