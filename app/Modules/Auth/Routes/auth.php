<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;

Route::prefix('auth')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
    Route::middleware(['auth:sanctum','role:1'])->get('/test-role', function () {
    return response()->json(['mensaje' => 'Acceso permitido']);
   });

});