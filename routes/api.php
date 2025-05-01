<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Files API
    Route::get('/files', [FileController::class, 'apiGetFiles']);
    Route::get('/files/shared', [FileController::class, 'apiGetSharedFiles']);
    Route::get('/files/{file}/shares', [FileController::class, 'apiGetFileShares']);
    
    // File upload/delete
    Route::post('/files/upload', [FileController::class, 'apiUploadFile']);
    Route::delete('/files/{file}', [FileController::class, 'apiDeleteFile']);
    
    // File sharing
    Route::post('/files/{file}/share', [FileController::class, 'apiShareFile']);
    Route::delete('/files/{file}/share/{user}', [FileController::class, 'apiRemoveShare']);
}); 