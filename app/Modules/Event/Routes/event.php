<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Event\Controllers\EventController;

Route::prefix('event')->group(function (){

Route::middleware(['auth:sanctum','role:2'])->group(function () { //Rol Docente
        Route::post('/crearEventoClase', [EventController::class, 'crearEventoClase']);
        Route::get('/obtenerEventosClase/{claseId}',[EventController::class, 'obtenerEventosClase']);
        Route::delete('/eliminarEventoClase/{eventoId}',[EventController::class, 'eliminarEventoClase']);
        Route::put('/actualizarEventoClase/{eventoId}',[EventController::class, 'actualizarEventoClase']); 
        Route::get('/mostrarEventosAsignados',[EventController::class, 'mostrarEventosAsignados']); 
});

Route::middleware(['auth:sanctum','role:1'])->group(function () { //Rol administrador
        Route::post('/crearEventoGeneral', [EventController::class, 'crearEventoGeneral']);
        Route::get('/obtenerEventosAdministrador',[EventController::class, 'obtenerEventosAdministrador']); //posiblemente se elimine en un futuro
        Route::put('/actualizarEventoGeneral/{eventoId}',[EventController::class, 'actualizarEventoGeneral']);
        Route::delete('/eliminarEventoGeneral/{eventoId}',[EventController::class, 'eliminarEventoGeneral']);
        Route::post('/asignarRegistradorEvento', [EventController::class, 'asignarRegistradorEvento']);
});

});