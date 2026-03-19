<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Group\Controllers\GroupController;

Route::prefix('group')->group(function (){

Route::middleware(['auth:sanctum','role:2'])->group(function () {
        Route::post('/importarGrupos', [GroupController::class, 'importarGrupos']);
    });

});