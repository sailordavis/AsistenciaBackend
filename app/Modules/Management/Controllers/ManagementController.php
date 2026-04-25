<?php

namespace App\Modules\Management\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Management\Services\ManagementService;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Alumno;
use App\Models\Administrador;

use Illuminate\Support\Facades\DB;

class ManagementController extends Controller
{
       //Metodo de register 
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

        if ($request->rol_id == 2) { //Registro de cuenta docente
            Docente::create([
                'usuario_id' => $usuario->id,
                'numero_nomina' => $request->numero_nomina
            ]);
        }

        if ($request->rol_id == 3) { //Registro de cuenta alumno
            Alumno::create([
                'usuario_id' => $usuario->id,
                'codigo_institucional' => $request->codigo_institucional,
                'carrera_id' => $request->carrera_id
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

//  GET / todos los usuarios
    public function index(ManagementService $service)
    {
        try {
            $usuarios = $service->listarUsuarios();

            return response()->json($usuarios);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

      //  GET / un usuario en especifico/{id}
    public function show($id, ManagementService $service)
    {
        try {
            $usuario = $service->mostrarUsuario($id);

            return response()->json($usuario);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
    
    //PUT /usuarios/{id}
    public function update(Request $request, $id, ManagementService $service)
{
    // Buscar usuario por correo
   // $usuario = Usuario::where('correoInstitucional', $correoInstitucional)->firstOrFail();

    // Obtener rol (request o BD)
    $rol = $request->input('rol_id', $usuario->rol_id);

    $data = $request->validate([
        'nombre' => 'sometimes|string|max:255',
        'apellidos' => 'sometimes|string|max:255',
        'correoInstitucional' => 'sometimes|email|unique:usuarios,correoInstitucional,' . $id,
        'password' => 'sometimes|string|min:6',
        'rol_id' => 'sometimes|exists:rols,id',

        'numero_nomina' => $rol == 2 ? 'required|string|max:50' : 'nullable',
        'codigo_institucional' => $rol == 3 ? 'required|string|max:50' : 'nullable',
    ]);

    try {
        $usuario = $service->actualizarUsuario($id, $data);

        return response()->json([
            'mensaje' => 'Usuario actualizado correctamente',
            'usuario' => $usuario
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 400);
    }
}


 //  DELETE /usuarios/{id}
    public function destroy($id, ManagementService $service)
    {
        try {
            $service->eliminarUsuario($id);

            return response()->json([
                'mensaje' => 'Usuario eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }


    public function asistenciasEvento($eventoId, ManagementService $service)
{
    try {
        $asistencias = $service->asistenciasPorEvento($eventoId);

        return response()->json($asistencias);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 403);
    }
}


public function buscar(Request $request)
{
    $correoInstitucional = $request->input('correoInstitucional');

    $usuario = Usuario::where('correoInstitucional', 'like', "%$correoInstitucional%")->first();

    return response()->json($usuario);
}

    

   
}

