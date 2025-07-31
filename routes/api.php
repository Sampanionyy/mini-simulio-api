<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; 
use App\Http\Controllers\ClientController; 
use App\Http\Controllers\Api\SimulationController; 

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'user']);
    Route::post('/simulations', [SimulationController::class, 'store']);
    Route::post('/simulations/client/{client}', [SimulationController::class, 'getByClientId']);

    Route::apiResource('clients', ClientController::class);
});