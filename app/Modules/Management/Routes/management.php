<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Management\Controllers\ManagementController;

Route::prefix('management' )->group(function (){

Route::middleware(['auth:sanctum','role:1'])->group(function () {
    //Seccion gestion de los usuarios parte del sistema
     Route::post('/register', [ManagementController::class, 'register']);
     Route::get('/listarUsuarios',[ManagementController::class, 'listarUsuarios']);
     Route::get('/listarUsuariosRol/{rolId}', [ManagementController::class, 'listarUsuariosRol']);
     Route::get('/buscarUsuario/{correo}',[ManagementController::class, 'buscarUsuario']);
     Route::get('/buscarDocente/{nomina}',[ManagementController::class, 'buscarDocente']);
     Route::get('/buscarAlumno/{codigo}',[ManagementController::class, 'buscarAlumno']);
     Route::get('/buscarAdministrador/{correo}',[ManagementController::class, 'buscarAdministrador']);
     Route::delete('/EliminarCuenta/{id}', [ManagementController::class, 'EliminarCuenta']);
     Route::put('/ActualizarCuenta/{id}', [ManagementController::class, 'ActualizarCuenta']);

     //Seccion gestion de las clases parte del sistema 
     Route::get('/mostrarClasesDocente/{docenteId}',[ManagementController::class, 'mostrarClasesDocente']);
     
     //Endpoints a editar o eliminar 
     Route::get('/asistenciasEvento{eventoId}',[ManagementController::class, 'asistenciasEvento']);
    });

});