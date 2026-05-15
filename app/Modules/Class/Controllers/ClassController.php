<?php
namespace App\Modules\Class\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Class\Services\ClassService;
use App\Models\Clase;
use Exception;

class ClassController extends Controller
{
    //Funciones para la gestion de clases 
    //OPeracion POST: Crear clase 
    public function crearClase(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
    ]);

    try {
        // Obtener docente automáticamente
        $docenteId = $this->getDocenteId();

        //  Crear clase
        $clase = Clase::create([
            'nombre' => $request->nombre,
            'docente_id' => $docenteId
        ]);

        return response()->json([
            'message' => 'Clase creada correctamente',
            'clase' => $clase
        ], 201);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
    //Operacion GET: MostrarClases
    public function mostrarClases(ClassService $service)
    {
        try {
        // Obtener docente automáticamente
            $docenteId = $this->getDocenteId();
            $clases = $service->mostrarClases($docenteId);

            return response()->json($clases);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    //Operacion GET: Mostrar una clase del docente con nombre via url 
    public function buscarClase($nombre, ClassService $service)
    {
        try {
            $docenteId = $this->getDocenteId();
            $clase = $service->mostrarUnaClase($nombre,$docenteId);

            return response()->json($clase);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
    //Operacion DELETE: Eliminar una clase del docente con id via url
    public function eliminarClase($id, ClassService $service)
{
    try {

        $docenteId = $this->getDocenteId();

        $service->eliminarClase($id, $docenteId);

        return response()->json([
            'message' => 'Clase eliminada correctamente'
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 404);

    }
}

   //Operacion PUT: Actualizar una clase del docente con id via url 
   public function actualizarClase($id, Request $request, ClassService $service)
{
    try {

        // Obtener docente autenticado
        $docenteId = $this->getDocenteId();

        // Validar datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        // Actualizar clase
        $claseActualizada = $service->actualizarClase(
            $id,
            $docenteId,
            $validated
        );

        return response()->json([
            'message' => 'Clase actualizada correctamente',
            'clase' => $claseActualizada
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 400);

    }
}

   //Operacion GET: Mostrar lista de alumnos registrados a clase

   public function mostrarAlumnosClase($claseId, ClassService $service)
{
    try {

        $docenteId = $this->getDocenteId();

        $alumnos = $service->mostrarAlumnosClase(
            $claseId,
            $docenteId
        );

        return response()->json($alumnos, 200);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 404);

    }
}


//Funciones auxiliares
private function getDocenteId()
{
    // Usuario autenticado
    $usuario = auth()->user();

    // Obtener docente relacionado
    $docente = $usuario->docente;

    // Validar que sí sea docente
    if (!$docente) {

        abort(response()->json([
            'message' => 'El usuario no es docente'
        ], 403));

    }

    return $docente->id;
}

//Funcion para el rol alumno 
//Operacion GET: Mostrar las clases en las que el alumno asiste

public function obtenerMisClases(
    ClassService $service
) {

    try {

        $clases = $service->obtenerMisClases(
            auth()->id()
        );

        return response()->json([

            'message' => 'Clases obtenidas correctamente',

            'clases' => $clases

        ], 200);

    } catch (\Exception $e) {

        return response()->json([

            'message' => $e->getMessage()

        ], 400);
    }
}
}