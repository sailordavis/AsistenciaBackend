<?php

namespace App\Modules\Management\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Management\Services\ManagementService;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Alumno;
use App\Models\Administrador;
use Illuminate\Validation\ValidationException; //Necesario para que el sistema pueda reaccionar a las excepciones disparadas

use Illuminate\Support\Facades\DB;

class ManagementController extends Controller
{
    //Funciones para la gestion de usuarios

    //Operacion POST: Registrar Usuario
       public function register(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'apellidos' => 'required|string|max:255',
        'correoinstitucional' => 'required|email|unique:usuarios,correoinstitucional',
        'password' => 'required|min:4',
        'rol_id' =>  'required|in:1,2,3',
        'numero_nomina' => 'required_if:rol_id,2',
        'codigo_institucional' => 'required_if:rol_id,3'
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

// Operacion  GET: obtener todos los usuarios
    public function listarUsuarios(ManagementService $service)
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
   // Operacion GET:  obtener todos los usuarios segun rol indicado
   public function listarUsuariosRol(ManagementService $service, $rolId)
{
    try {
        // Validar que el rol sea válido
        if (!in_array($rolId, [1, 2, 3])) {
            throw new \Exception("Rol inválido. Usa 1=Admin, 2=Docente, 3=Alumno");
        }

        // Obtener usuarios por rol
        $usuarios = Usuario::where('rol_id', $rolId)->get();

        // Validar si hay resultados
        if ($usuarios->isEmpty()) {
            return response()->json([
                'mensaje' => 'No hay usuarios registrados con este rol'
            ], 404);
        }

        // Retornar lista
        return response()->json($usuarios, 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

      // Operacion GET: obtener un usuario en especifico (independiente del rol)/correo
    public function buscarUsuario(Request $request, ManagementService $service)
{
    try {
        $correo = $request->query('correo');

        if (!$correo) {
            return response()->json([
                'error' => 'Debe proporcionar un correo institucional'
            ], 400);
        }

        $usuario = $service->mostrarUsuario($correo);

        if ($usuario->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se encontro ningun usuario con los datos que proporciono'
            ], 404);
        }

        return response()->json($usuario, 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }

}
//Operacio GET: obtener un docente en especifico /numero de nomina
public function buscarDocente(Request $request, ManagementService $service)
{
    try {
        $nomina = $request->query('nomina');

        if (!$nomina) {
            return response()->json([
                'error' => 'Debe proporcionar un número de nómina'
            ], 400);
        }

        $docente = $service->mostrarDocente($nomina);

        if ($docente->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se encontraro ningun docente con los datos que proporciono'
            ], 404);
        }

        return response()->json($docente, 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
 //Operacion GET: obtener un alumno en especifico /codigo institucional
  public function buscarAlumno(Request $request, ManagementService $service)
{
    try {
        $codigo = $request->query('codigo');

        if (!$codigo) {
            return response()->json([
                'error' => 'Debe proporcionar un codigo institucional'
            ], 400);
        }

        $alumno = $service->mostrarAlumno($codigo);

        if ($alumno->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se encontro ningun alumno con los datos que proporciono'
            ], 404);
        }

        return response()->json($alumno, 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
 //Operacion GET: obtener un administrador en especifico /correo
public function buscarAdministrador(Request $request, ManagementService $service)
{
    try {
        $correo = $request->query('correo');

        if (!$correo) {
            return response()->json([
                'error' => 'Debe proporcionar un correo institucional'
            ], 400);
        }

        $administrador = $service->mostrarAdministrador($correo);

        if ($administrador->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se encontro ningun administrador con los datos que proporciono'
            ], 404);
        }

        return response()->json($administrador, 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
//Operacion DELETE: eliminar una cuenta usuario especifico /id
public function EliminarCuenta($id)
{
    $usuario = Usuario::find($id);

    if (!$usuario) {
        return response()->json([
            'message' => 'Usuario no encontrado'
        ], 404);
    }

    $usuario->delete();

    return response()->json([
        'message' => 'Usuario eliminado correctamente'
    ], 200);
}
//Operacion PUT: editar una cuenta usuario especifico /id
public function ActualizarCuenta(Request $request, $id)
{
    // Buscar usuario
    $usuario = Usuario::find($id);

    if (!$usuario) {
        return response()->json([
            'message' => 'Usuario no encontrado'
        ], 404);
    }

    //  Actualizar campos generales (usuarios)
    $usuario->nombre = $request->input('nombre', $usuario->nombre);
    $usuario->apellidos = $request->input('apellidos', $usuario->apellidos);
    $usuario->correoinstitucional = $request->input('correoinstitucional', $usuario->correoinstitucional);
    $usuario->save();

    //  Validar rol y actualizar tabla relacionada
    if ($usuario->rol_id == 3) { // Estudiante

        $alumno = Alumno::where('usuario_id', $usuario->id)->first();

        if ($alumno) {
            $alumno->codigo_institucional = $request->input(
                'codigo_institucional',
                $alumno->codigo_institucional
            );
            $alumno->save();
        }

    } elseif ($usuario->rol_id == 2) { // Docente

        $docente = Docente::where('usuario_id', $usuario->id)->first();

        if ($docente) {
            $docente->numero_nomina = $request->input(
                'numero_nomina',
                $docente->numero_nomina
            );
            $docente->save();
        }
    }

    return response()->json([
        'message' => 'Usuario actualizado correctamente',
        'data' => $usuario
    ], 200);
}





    //Funciones a editar o eliminar en un futuro
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

