<?php
namespace App\Modules\Class\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Class\Services\ClassService;
use Exception;

class ClassController extends Controller
{
    // POST: MostrarClases
    public function listarClases(ClassService $service)
    {
        try {
            $clases = $service->listarClases();

            return response()->json($clases);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    //  GET /clases/{nombre}
    public function mostrarClase($nombre, ClassService $service)
    {
        try {
            $clase = $service->mostrarClase($nombre);

            return response()->json($clase);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }

    //  POST /clases
    public function store(Request $request, ClassService $service)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        try {
            $clase = $service->crearClase($data);

            return response()->json([
                'mensaje' => 'Clase creada correctamente',
                'clase' => $clase
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // PUT/clases/{nombre}
    public function update(Request $request, $nombre, ClassService $service)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|string|max:255',
        ]);

        try {
            $clase = $service->actualizarClase($id, $data);

            return response()->json([
                'mensaje' => 'Clase actualizada correctamente',
                'clase' => $clase
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // DELETE /clases/{nombre}
    public function destroy($nombre, ClassService $service)
    {
        try {
            $service->eliminarClase($nombre);

            return response()->json([
                'mensaje' => 'Clase eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }


    public function listaAlumnos($nombre, ClassService $service)
{
    try {
        $lista = $service->listaAlumnosPorClase($nombre);

        return response()->json($lista);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 400);
    }
}
}