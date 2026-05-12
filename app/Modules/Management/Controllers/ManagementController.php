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
    public function buscarUsuario( ManagementService $service, $correo)
{
    try {
        $usuario = $service->mostrarUsuario($correo);

        return response()->json($usuario, 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }

}
//Operacio GET: obtener un docente en especifico /numero de nomina
public function buscarDocente(ManagementService $service, $nomina)
{
    try {

        $docente = $service->mostrarDocente($nomina);

        return response()->json($docente, 200);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 404);

    }
}
 //Operacion GET: obtener un alumno en especifico /codigo institucional
  public function buscarAlumno( ManagementService $service, $codigo)
{
    try {
        $alumno = $service->mostrarAlumno($codigo);

        return response()->json($alumno, 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
 //Operacion GET: obtener un administrador en especifico /correo
public function buscarAdministrador( ManagementService $service, $correo)
{
    try {
        $administrador = $service->mostrarAdministrador($correo);

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

//Funcion para la gestion de las clases registradas en la clase
//Operacion GET: MostrarClases del docente seleccionado via id por url 
    public function mostrarClasesDocente(ClassService $service, $docenteId)
    {
        try {
            $clases = $service->mostrarClasesDocente($docenteId);

            return response()->json($clases);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }





    
   


 
 //Funciones a editar o eliminar en un futuro

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




    

   
}

