<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Management\Controllers\ManagementController;

Route::prefix('management' )->group(function (){

Route::middleware(['auth:sanctum','role:1'])->group(function () {
    //Seccion para la gestion de los usuarios parte del sistema
     Route::post('/register', [ManagementController::class, 'register']);
     Route::get('/listarUsuarios',[ManagementController::class, 'listarUsuarios']);
     Route::get('/listarUsuariosRol/{rolId}', [ManagementController::class, 'listarUsuariosRol']);
     Route::get('/buscarUsuario/{correo}',[ManagementController::class, 'buscarUsuario']);
     Route::get('/buscarDocente/{nomina}',[ManagementController::class, 'buscarDocente']);
     Route::get('/buscarAlumno/{codigo}',[ManagementController::class, 'buscarAlumno']);
     Route::get('/buscarAdministrador/{correo}',[ManagementController::class, 'buscarAdministrador']);
     Route::delete('/EliminarCuenta/{id}', [ManagementController::class, 'EliminarCuenta']);
     Route::put('/ActualizarCuenta/{id}', [ManagementController::class, 'ActualizarCuenta']);

     //Seccion para la visualizacion global de clases
     Route::get('/mostrarClasesDocente/{docenteId}',[ManagementController::class, 'mostrarClasesDocente']);
     Route::get('/mostrarAlumnosClase/{claseId}',[ManagementController::class, 'mostrarAlumnosClase']); 

     //Seccion para la visualizacion global de eventos 
      Route::get('/obtenerEventosGenerales',[ManagementController::class, 'obtenerEventosGenerales']);
      Route::get('/obtenerEventosClase/{claseId}',[ManagementController::class, 'obtenerEventosClase']); 
      Route::get('/mostrarEventosAsignados/{docenteId}',[ManagementController::class, 'mostrarEventosAsignados']);

    //Seccion para la visualizacion global de asistencias
    Route::get('/obtenerAsistenciasEvento/{eventoId}',[ManagementController::class, 'obtenerAsistenciasEvento']); 
     
     
    });

});