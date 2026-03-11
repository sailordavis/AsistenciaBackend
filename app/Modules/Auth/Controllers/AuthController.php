<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Alumno;
use App\Models\Administrador;

class AuthController extends Controller
{

    public function register(Request $request)
    {

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'correoInstitucional' => 'required|email|unique:usuarios,correoInstitucional',
            'password' => 'required|min:6',
            'rol_id' =>  'required|in:2,3',
             'numero_nomina' => 'required_if:rol_id,2',
            'codigo_institucional' => 'required_if:rol_id,3'
        ]);


        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'correoInstitucional' => $request->correoInstitucional,
            'password' => $request->password,
            'rol_id' => $request->rol_id
        ]);


        // si es docente
        if ($request->rol_id == 2) {

            Docente::create([
                'usuario_id' => $usuario->id,
                'numero_nomina' => $request->numero_nomina
            ]);
        }

        // si es alumno
        if ($request->rol_id == 3) {

            Alumno::create([
                'usuario_id' => $usuario->id,
                'codigo_institucional' => $request->codigo_institucional
            ]);
        }
        
        //si es administrador 
        if($request->rol_id == 1){

            Administrador::create([
                'usuario_id' => $usuario->id 
            ]);
        }

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'usuario' => $usuario
        ], 201);
    }



    public function login(Request $request)
    {

        $request->validate([
            'correoInstitucional' => 'required|email',
            'password' => 'required'
        ]);

        $usuario = Usuario::where('correoInstitucional', $request->correoInstitucional)->first();

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
}