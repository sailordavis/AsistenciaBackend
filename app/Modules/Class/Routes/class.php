<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Class\Controllers\ClassController;

Route::prefix('class')->group(function (){

Route::middleware(['auth:sanctum','role:2'])->group(function () {
         Route::post('/store', [EventController::class, 'store']);
        Route::get('/listarClases',[EventController::class, 'listarClases']);
        Route::get('/mostrarClase{nombre}',[EventController::class, 'mostrarClase']);
        Route::put('/update{nombre}',[EventController::class, 'update']);
        Route::delete('/destroy{nombre}',[EventController::class, 'destroy']);
        Route::get('/listaAlumnos{nombre}',[EventController::class, 'listaAlumnos']);
    });

});