<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users', [UserController::class, 'index'])->middleware('permission:view users');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:create users');
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:view users');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:update users');
    Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('permission:update users');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete users');
});
