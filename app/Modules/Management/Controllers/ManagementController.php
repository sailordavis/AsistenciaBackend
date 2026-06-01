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
   public function listarUsuariosRol( $rolId)
{
    try {
        // Roles permitidos
        $rolesPermitidos = [1, 2, 3];

        if (!in_array($rolId, $rolesPermitidos)) {

            return response()->json([
                'success' => false,
                'data' => 'Rol inválido'
            ], 400);

        }

        // Obtener usuarios por rol
        $usuarios = Usuario::with(['alumno', 'docente'])
        ->where('rol_id', $rolId)
        ->get();

       return response()->json([
            'success' => true,
            'data' => $usuarios
        ], 200);

    } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error al obtener usuarios',
            'error' => $e->getMessage()
        ], 500);
    }
}



     // Operacion GET: buscar usuarios por correo
public function buscarUsuarios(
    ManagementService $service,
    $correo
) {

    try {

        $usuarios = $service->mostrarUsuarios($correo);

        return response()->json([
            'success' => true,
            'data' => $usuarios
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);

    }
}
//Operacio GET: obtener un docente en especifico /numero de nomina
public function buscarDocentes(ManagementService $service, $correo)
{
    try {

        $docentes = $service->mostrarDocentes($correo);

        return response()->json([
            'success' => true,
            'data' => $docentes
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'data' => $e->getMessage()
        ], 500);

    }
}
 //Operacion GET: obtener un alumno en especifico /codigo institucional
  public function buscarAlumnos( ManagementService $service, $correo)
{
    try {
        $alumnos = $service->mostrarAlumnos($correo);

         return response()->json([
            'success' => true,
            'data' => $alumnos
        ], 200);

    } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'data' => $e->getMessage()
        ], 500);
    }
}
 
//Operacion DELETE: eliminar una cuenta usuario especifico /id

public function EliminarCuenta($id)
{
    try {

        $usuario = Usuario::find($id);

        if (!$usuario) {

            return response()->json([

                'success' => false,
                'message' => 'Usuario no encontrado'

            ], 404);
        }

        $usuario->delete();

        return response()->json([

            'success' => true,
            'message' => 'Usuario eliminado correctamente'

        ], 200);

    } catch (\Exception $e) {

        return response()->json([

            'success' => false,
            'message' => 'Error al eliminar el usuario',
            'error' => $e->getMessage()

        ], 500);
    }
}
// Operacion PUT: editar una cuenta usuario especifico /id
public function ActualizarCuenta(Request $request, $id)
{
    try {

        // Buscar usuario
        $usuario = Usuario::find($id);

        if (!$usuario) {

            return response()->json([

                'success' => false,
                'message' => 'Usuario no encontrado'

            ], 404);
        }

        // Actualizar campos generales (usuarios)
        $usuario->nombre = $request->input(
            'nombre',
            $usuario->nombre
        );

        $usuario->apellidos = $request->input(
            'apellidos',
            $usuario->apellidos
        );

        $usuario->correoinstitucional = $request->input(
            'correoinstitucional',
            $usuario->correoinstitucional
        );

        $usuario->save();

        // Validar rol y actualizar tabla relacionada
        if ($usuario->rol_id == 3) { // Estudiante

            $alumno = Alumno::where(
                'usuario_id',
                $usuario->id
            )->first();

            if ($alumno) {

                $alumno->codigo_institucional = $request->input(

                    'codigo_institucional',
                    $alumno->codigo_institucional

                );

                $alumno->save();
            }

        } elseif ($usuario->rol_id == 2) { // Docente

            $docente = Docente::where(
                'usuario_id',
                $usuario->id
            )->first();

            if ($docente) {

                $docente->numero_nomina = $request->input(

                    'numero_nomina',
                    $docente->numero_nomina

                );

                $docente->save();
            }
        }

        return response()->json([

            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'data' => $usuario

        ], 200);

    } catch (\Exception $e) {

        return response()->json([

            'success' => false,
            'message' => 'Error al actualizar el usuario',
            'error' => $e->getMessage()

        ], 500);
    }
}


//Funciones para la visualizacion global de clases
//Operacion GET: MostrarClases del docente seleccionado via id por url 
    public function mostrarClasesDocente(ManagementService $service, $docenteId)
    {
        try {
            $clases = $service->mostrarClasesDocente($docenteId);

            return response()->json([
            'success' => true,
            'data' => $clases
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

      //Operacion GET: Mostrar lista de alumnos registrados a clase

   public function mostrarAlumnosClase($claseId, ManagementService $service)
{
    try {

        $alumnos = $service->mostrarAlumnosClase(
            $claseId
        );

        return response()->json([
            'success' => true,
            'data' => $alumnos
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 404);

    }
}


//Operacion GET: Obtener las clases alas que pertenece el alumno 

public function obtenerClasesAlumno(
    $alumnoId,
    ManagementService $service
) {

    try {

        $clases = $service
            ->obtenerClasesAlumno(
                $alumnoId
            );

         return response()->json([
            'success' => true,
            'data' => $clases
        ], 200);

    } catch (\Exception $e) {

        return response()->json([

            'message' => $e->getMessage()

        ], 400);
    }
}

//Funciones para la visualizacion global de eventos
//Operacion GET: MOstrar todos los eventos generales
public function obtenerEventosGenerales( ManagementService $service) {

    try {

        $eventos =  $service->obtenerEventosGenerales();

       return response()->json([
            'success' => true,
            'data' => $eventos
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}

   //Operacion GET: Obtener los eventos de una clase en especifico
    public function obtenerEventosClase($claseId, ManagementService $service )
{
    try {
        
        $eventos =  $service->obtenerEventosClase(
            $claseId
        );

        return response()->json([
            'success' => true,
            'data' => $eventos
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);

    }
}


//Operacion GET: Mostrar los eventos que le asignaron al docente
public function mostrarEventosAsignados($docenteId, ManagementService $service)
{
    try {

        $eventos = $service->mostrarEventosAsignados(
            $docenteId
        );

        return response()->json([
            'success' => true,
            'data' => $eventos
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);

    }
}


//Funciones para la visualizacion global de asistencias


// Operacion GET: Obtencion de lista de asistencias globales de eventos 

public function obtenerAsistenciasEvento(
    $eventoId,
    ManagementService $service
) {

    try {

        $asistencias = $service
            ->obtenerAsistenciasEvento(
                $eventoId
            );

        return response()->json([
            'success' => true,
            'data' => $asistencias
        ], 200);

    } catch (\Exception $e) {

        return response()->json([

            'message' => $e->getMessage()

        ], 400);
    }
}


// Operacion GET: Obtener asistencias propias del alumno en una clase en especifico

public function obtenerAsistenciasClaseAlumno(
    $alumnoId,
    $claseId,
    ManagementService $service
) {

    try {

        $asistencias = $service
            ->obtenerAsistenciasClaseAlumno(

                $alumnoId,

                $claseId
            );

           return response()->json([
            'success' => true,
            'data' => $asistencias
        ], 200);

    } catch (\Exception $e) {

        return response()->json([

            'message' => $e->getMessage()

        ], 400);
    }
}


// Operacion GET: Obtener asistencias de eventos generales del alumno 

public function obtenerAsistenciasEventosGeneralesAlumno(
    $alumnoId,
    ManagementService $service
) {

    try {

        $asistencias = $service
            ->obtenerAsistenciasEventosGeneralesAlumno(
                $alumnoId
            );

        return response()->json([
            'success' => true,
            'data' => $asistencias
        ], 200);

    } catch (\Exception $e) {

        return response()->json([

            'message' => $e->getMessage()

        ], 400);
    }
}

    
   


 





    

   
}

