<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Alumno;
use App\Models\Administrador;
use Illuminate\Validation\ValidationException; //Necesario para que el sistema pueda reaccionar a las excepciones disparadas

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Exception;

class AuthController extends Controller
{

//Funcion que se eliminara en un futuro 

   public function register(Request $request)
{

    $request->validate([
        'nombre' => 'required|string|max:255',
        'apellidos' => 'required|string|max:255',
        'correoinstitucional' => 'required|email|unique:usuarios,correoinstitucional',
        'password' => 'required|min:4',
        'rol_id' =>  'required|in:1,2,3',
        'numero_nomina' => 'required_if:rol_id,2',
        'codigo_institucional' => 'required_if:rol_id,3',
    ]);

    DB::beginTransaction();

    try {

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'correoinstitucional' => $request->correoinstitucional,
            'password' => $request->password,
            'rol_id' => $request->rol_id
        ]);

        if ($request->rol_id == 2) { //Registro de cuenta docente
            Docente::create([
                'usuario_id' => $usuario->id,
                'numero_nomina' => $request->numero_nomina
            ]);
        }

        if ($request->rol_id == 3) { //Registro de cuenta alumno
            Alumno::create([
                'usuario_id' => $usuario->id,
                'codigo_institucional' => $request->codigo_institucional
            ]);
        }

        if ($request->rol_id == 1) { //Registro de cuenta administrador
            Administrador::create([
                'usuario_id' => $usuario->id
            ]);
        }

        DB::commit();

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'usuario' => $usuario
        ], 201);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}



public function login(Request $request)
{
    $request->validate([
        'correoinstitucional' => 'required|email',
        'password' => 'required'
    ]);

    $usuario = Usuario::where('correoinstitucional', $request->correoinstitucional)->first();

    if (!$usuario || !Hash::check($request->password, $usuario->password)) {
        return response()->json([
            'message' => 'Credenciales incorrectas'
        ], 401);
    }

    // eliminar tokens anteriores
    $usuario->tokens()->delete();

    // crear nuevo token
    $token = $usuario->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login exitoso',
        'token' => $token,
        'usuario' => $usuario
    ]);
}

public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Sesión cerrada'
    ]);
}

public function me(Request $request)
{
    return response()->json([
        'user' => $request->user()
    ]);
}


public function forgotPassword(Request $request)
{
    $request->validate([
        'correoinstitucional' => 'required|email'
    ]);

    $usuario = Usuario::where(
        'correoinstitucional',
        $request->correoinstitucional
    )->first();

    if (!$usuario) {
        return response()->json([
            'message' => 'Usuario no encontrado'
        ], 404);
    }

    $token = Str::random(60);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->correoinstitucional],
        [
            'token' => $token,
            'created_at' => Carbon::now()
        ]
    );

    //  LINK que recibirá el usuario
    $link = url("/reset-password?token=$token&email={$request->correoinstitucional}");

    //  Enviar correo
    Mail::raw("Da clic en el siguiente enlace para restablecer tu contraseña: $link", function ($message) use ($request) {
        $message->to($request->correoinstitucional)
                ->subject('Recuperación de contraseña');
    });

    return response()->json([
        'message' => 'Se ha enviado un enlace a tu correo'
    ]);
}


public function resetPassword(Request $request)
{
    $request->validate([
        'correoinstitucional' => 'required|email',
        'token' => 'required',
        'password' => 'required|min:6|confirmed'
    ]);

    $reset = DB::table('password_reset_tokens')
        ->where('email', $request->correoinstitucional)
        ->where('token', $request->token)
        ->first();

    if (!$reset) {
        return response()->json([
            'message' => 'Token inválido'
        ], 400);
    }

   
    if (Carbon::parse($reset->created_at)->addMinutes(60)->isPast()) {
        return response()->json([
            'message' => 'Token expirado'
        ], 400);
    }

    $usuario = Usuario::where(
        'correoinstitucional',
        $request->correoinstitucional
    )->first();

    
    $usuario->password = $request->password;
    $usuario->save();

    // eliminar token usado
    DB::table('password_reset_tokens')
        ->where('email', $request->correoinstitucional)
        ->delete();

    return response()->json([
        'message' => 'Contraseña actualizada correctamente'
    ]);
}


}