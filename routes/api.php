<?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\UserController;

// Route::post('/register', [UserController::class, 'register']);



require base_path('app/Modules/Auth/Routes/auth.php');
require base_path('app/Modules/Usuarios/Routes/usuarios.php');
require base_path('app/Modules/Eventos/Routes/eventos.php');

