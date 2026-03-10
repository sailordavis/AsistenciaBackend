<?php
//use App\Http\Controllers\Api\UserController;

//Route::post('/register', [UserController::class, 'register']);
 use Illuminate\Support\Facades\Route;

 Route::get('/api/test', function () {
     return response()->json([
     'mensaje' => 'API funcionando correctamente'
    ]);
 });


