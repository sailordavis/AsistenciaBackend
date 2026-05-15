<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Report\Controllers\ReportController;

Route::prefix('report')->group(function (){

Route::middleware(['auth:sanctum','role:2'])->group(function () { //Rol docente
   Route::get('/obtenerAsistenciasEventoClaseDocente/{eventoId}',[ReportController::class, 
   'obtenerAsistenciasEventoClaseDocente']);
   Route::get('/obtenerAsistenciasEventosGeneralesDocente/{eventoId}',[ReportController::class, 
   'obtenerAsistenciasEventosGeneralesDocente']);
});

Route::middleware(['auth:sanctum','role:3'])->group(function () { //Rol alumno
  Route::get('/obtenerAsistenciasClaseAlumno/{claseId}',[ReportController::class, 'obtenerAsistenciasClaseAlumno']);
  Route::get('/obtenerAsistenciasEventosGenerales',[ReportController::class, 'obtenerAsistenciasEventosGenerales']);
});

});