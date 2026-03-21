<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Management\Controllers\ManagementController;

Route::prefix('management')->group(function (){

Route::middleware(['auth:sanctum','role:1'])->group(function () {
        Route::post('/importarAlumnos', [ManagementController::class, 'importarAlumnos']);
        Route::post('/importarDocentes', [ManagementController::class, 'importarDocentes']);
    });

});