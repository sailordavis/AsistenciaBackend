<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Management\Controllers\ManagementController;

Route::prefix('management' )->group(function (){

Route::middleware(['auth:sanctum','role:1'])->group(function () {
    //Seccion gestion de los usuarios parte del sistema
     Route::post('/register', [ManagementController::class, 'register']);
     Route::get('/listarUsuarios',[ManagementController::class, 'listarUsuarios']);
     Route::get('/listarUsuariosRol/{rolId}', [ManagementController::class, 'listarUsuariosRol']);
     Route::get('/buscarUsuario',[ManagementController::class, 'buscarUsuario']);
     Route::get('/buscarDocente',[ManagementController::class, 'buscarDocente']);
     Route::get('/buscarAlumno',[ManagementController::class, 'buscarAlumno']);
     Route::get('/buscarAdministrador',[ManagementController::class, 'buscarAdministrador']);
     Route::delete('/EliminarCuenta/{id}', [ManagementController::class, 'EliminarCuenta']);
     Route::put('/ActualizarCuenta/{id}', [ManagementController::class, 'ActualizarCuenta']);
     //Endpoints a editar o eliminar 
     Route::get('/show{id}',[ManagementController::class, 'show']);
     Route::put('/update{id}',[ManagementController::class, 'update']);
     Route::delete('/destroy{id}',[ManagementController::class, 'destroy']);
     Route::get('/asistenciasEvento{eventoId}',[ManagementController::class, 'asistenciasEvento']);
     Route::get('/buscar{correoInstitucional}',[ManagementController::class, 'buscar']);
    });

});