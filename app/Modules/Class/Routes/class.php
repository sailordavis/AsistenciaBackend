<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Class\Controllers\ClassController;

Route::prefix('class')->group(function (){

Route::middleware(['auth:sanctum','role:2'])->group(function () {
        //Seccion para la gestion de clases propias del docente
        Route::post('/crearClase', [ClassController::class, 'crearClase']);
        Route::get('/mostrarClases',[ClassController::class, 'mostrarClases']);
        Route::get('/buscarClase/{nombre}',[ClassController::class, 'buscarClase']);
        Route::delete('/eliminarClase/{id}', [ClassController::class, 'eliminarClase']);
        Route::put('/actualizarClase/{id}', [ClassController::class, 'actualizarClase']);
        Route::get('/mostrarAlumnosClase/{claseId}',[ClassController::class, 'mostrarAlumnosClase']);

        
        

        
    });

});