<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;



// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [FileController::class, 'dashboard'])->name('dashboard');
    
    // File operations
    Route::post('/files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::delete('/files/{file}', [FileController::class, 'delete'])->name('files.delete');
    
    // File sharing
    Route::get('/files/{file}/share', [FileController::class, 'showShareForm'])->name('files.share');
    Route::post('/files/{file}/share', [FileController::class, 'share']);
    Route::delete('/files/{file}/share/{user}', [FileController::class, 'removeShare'])->name('files.share.remove');
});
