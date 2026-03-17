<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Alumno;
use App\Models\Administrador;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{

   public function register(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'apellidos' => 'required|string|max:255',
        'correoInstitucional' => 'required|email|unique:usuarios,correoInstitucional',
        'password' => 'required|min:6',
        'rol_id' =>  'required|in:1,2,3',
        'numero_nomina' => 'required_if:rol_id,2',
        'codigo_institucional' => 'required_if:rol_id,3'
    ]);

    DB::beginTransaction();

    try {

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'correoInstitucional' => $request->correoInstitucional,
            'password' => $request->password,
            'rol_id' => $request->rol_id
        ]);

        if ($request->rol_id == 2) {
            Docente::create([
                'usuario_id' => $usuario->id,
                'numero_nomina' => $request->numero_nomina
            ]);
        }

        if ($request->rol_id == 3) {
            Alumno::create([
                'usuario_id' => $usuario->id,
                'codigo_institucional' => $request->codigo_institucional
            ]);
        }

        if ($request->rol_id == 1) {
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






}