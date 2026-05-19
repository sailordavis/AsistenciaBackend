<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Management\Controllers\ManagementController;

Route::prefix('management' )->group(function (){

Route::middleware(['auth:sanctum','role:1'])->group(function () {
    //Seccion para la gestion de los usuarios parte del sistema
     Route::post('/register', [ManagementController::class, 'register']);
     Route::get('/listarUsuarios',[ManagementController::class, 'listarUsuarios']);
     Route::get('/listarUsuariosRol/{rolId}', [ManagementController::class, 'listarUsuariosRol']);
     Route::get('/buscarUsuarios/{correo}',[ManagementController::class, 'buscarUsuarios']);
     Route::get('/buscarDocentes/{nomina}',[ManagementController::class, 'buscarDocentes']); 
     Route::get('/buscarAlumnos/{codigo}',[ManagementController::class, 'buscarAlumnos']);   
     Route::delete('/EliminarCuenta/{id}', [ManagementController::class, 'EliminarCuenta']);
     Route::put('/ActualizarCuenta/{id}', [ManagementController::class, 'ActualizarCuenta']);

     //Seccion para la visualizacion global de clases
     Route::get('/mostrarClasesDocente/{docenteId}',[ManagementController::class, 'mostrarClasesDocente']);
     Route::get('/mostrarAlumnosClase/{claseId}',[ManagementController::class, 'mostrarAlumnosClase']); 
     Route::get('/obtenerClasesAlumno/{alumnoId}',[ManagementController::class, 'obtenerClasesAlumno']);
     //Seccion para la visualizacion global de eventos 
      Route::get('/obtenerEventosGenerales',[ManagementController::class, 'obtenerEventosGenerales']);
      Route::get('/obtenerEventosClase/{claseId}',[ManagementController::class, 'obtenerEventosClase']); 
      Route::get('/mostrarEventosAsignados/{docenteId}',[ManagementController::class, 'mostrarEventosAsignados']);

    //Seccion para la visualizacion global de asistencias
    Route::get('/obtenerAsistenciasEvento/{eventoId}',[ManagementController::class, 'obtenerAsistenciasEvento']); 
    
    Route::get('/obtenerAsistenciasClaseAlumno/{alumnoId}/{claseId}',
    [ManagementController::class, 'obtenerAsistenciasClaseAlumno']); 

    Route::get('/obtenerAsistenciasEventosGeneralesAlumno/{alumnoId}',
    [ManagementController::class, 'obtenerAsistenciasEventosGeneralesAlumno']); 
    });

});