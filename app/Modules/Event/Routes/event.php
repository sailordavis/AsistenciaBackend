<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Event\Controllers\EventController;

Route::prefix('event')->group(function (){

Route::middleware(['auth:sanctum','role:1|2'])->group(function () {
        Route::post('/crearEvento', [EventController::class, 'crearEvento']);
        Route::get('/index',[EventController::class, 'index']);
        Route::get('/show{titulo}',[EventController::class, 'show']);
        Route::put('/update{titulo}',[EventController::class, 'update']);
        Route::delete('/destroy{titulo}',[EventController::class, 'destroy']);

    });

});