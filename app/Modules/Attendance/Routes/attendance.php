<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Attendance\Controllers\AttendanceController;

Route::prefix('attendance')->group(function (){

Route::middleware(['auth:sanctum','role:2'])->group(function () { //Rol docente
    Route::post('/buscarAlumnos', [AttendanceController::class, 'buscarAlumnos']);
    Route::post('/registrarAsistenciaManual', [AttendanceController::class, 'registrarAsistenciaManual']);   
});

Route::middleware(['auth:sanctum','role:3'])->group(function () { //Rol alumno
  Route::post('/registrarAsistenciaQR', [AttendanceController::class, 'registrarAsistenciaQR']); 
});

});