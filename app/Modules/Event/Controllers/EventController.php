<?php
namespace App\Modules\Event\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Event\Services\EventService;
use Exception;

class EventController extends Controller
{
    //  GET /eventos
    public function index(EventService $service)
    {
        try {
            $eventos = $service->listarEventos();

            return response()->json($eventos);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    //  GET /eventos/{titulo}
    public function show($titulo, EventService $service)
    {
        try {
            $evento = $service->mostrarEvento($titulo);

            return response()->json($evento);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }

    //  POST /eventos
    public function crearEvento(Request $request, EventService $service)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'tipo' => 'required|in:clase,general'
        ]);

        try {
            $evento = $service->crearEvento($data);

            return response()->json([
                'mensaje' => 'Evento creado correctamente',
                'evento' => $evento
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    //  PUT/PATCH /eventos/{titulo}
    public function update(Request $request, $titulo, EventService $service)
    {
        $data = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'fecha' => 'sometimes|date',
            'hora_inicio' => 'sometimes',
            'hora_fin' => 'sometimes',
            'tipo' => 'sometimes|in:clase,general'
        ]);

        try {
            $evento = $service->actualizarEvento($titulo, $data);

            return response()->json([
                'mensaje' => 'Evento actualizado correctamente',
                'evento' => $evento
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    //  DELETE /eventos/{titulo}
    public function destroy($titulo, EventService $service)
    {
        try {
            $service->eliminarEvento($titulo);

            return response()->json([
                'mensaje' => 'Evento eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
