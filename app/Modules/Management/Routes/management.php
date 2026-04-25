<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Management\Controllers\ManagementController;

Route::prefix('management')->group(function (){

Route::middleware(['auth:sanctum','role:1'])->group(function () {
     Route::post('/register', [ManagementController::class, 'register']);
     Route::get('/index',[ManagementController::class, 'index']);
     Route::get('/show{id}',[ManagementController::class, 'show']);
     Route::put('/update{id}',[ManagementController::class, 'update']);
     Route::delete('/destroy{id}',[ManagementController::class, 'destroy']);
     Route::get('/asistenciasEvento{eventoId}',[ManagementController::class, 'asistenciasEvento']);
     Route::get('/buscar{correoInstitucional}',[ManagementController::class, 'buscar']);
    });

});