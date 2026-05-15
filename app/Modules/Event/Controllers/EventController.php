<?php
namespace App\Modules\Event\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Event\Services\EventService;
use Exception;

class EventController extends Controller


{
    protected $eventoService;

    //Metodo constructor de la clase
    public function __construct(EventService $eventoService)
    {
        $this->eventoService = $eventoService;
    }

    //Funciones para la gestion de eventos para Clases del Docente
   //Operacion POST: Creacion de Evento 
    public function crearEventoClase(Request $request)
    {
      $request->validate([
            'titulo' => 'required|string|max:255',
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'clase_id' => 'required|exists:clases,id',

            // Opcionales
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'radio_permitido' => 'nullable|integer|min:1',
            'direccion_texto' => 'nullable|string|max:255',
        ]);

     try{
        $docenteId = $this->getDocenteId();
        $evento = $this->eventoService
            ->crearEventoClase(
                $request->all(),
                $docenteId
            );

    return response()->json([
            'message' => 'Evento creado correctamente',
            'evento' => $evento
        ], 201);

     }catch  (\Exception $e){
         return response()->json([
                'error' => $e->getMessage()
            ], 400);
     }
    }
    
    //Operacion GET: Obtener los eventos de una clase en especifico
    public function obtenerEventosClase($claseId)
{
    try {
        $docenteId = $this->getDocenteId();
        $eventos = $this->eventoService->obtenerEventosClase(
            $claseId,
            $docenteId
        );

        return response()->json([
            'message' => 'Eventos obtenidos correctamente',
            'eventos' => $eventos
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);

    }
}

//Operacion DELETE: Eliminar un evento con el id como referencia
   public function eliminarEventoClase($eventoId)
{
    try {
        $docenteId = $this->getDocenteId();
        $this->eventoService->eliminarEventoClase(
            $eventoId,
            $docenteId
        );

        return response()->json([
            'message' => 'Evento eliminado correctamente'
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);

    }
}


//Operacion PUT: Actualizar o modificar un evento con el id como referencia
public function actualizarEventoClase($eventoId, Request $request)
{
    $request->validate([
            'titulo' => 'required|string|max:255',
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',

            // opcionales (ubicación)
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'radio_permitido' => 'nullable|integer|min:1',
            'direccion_texto' => 'nullable|string|max:255',
        ]);
    try {

         $docenteId = $this->getDocenteId();
        $evento = $this->eventoService->actualizarEventoClase(
            $eventoId,
            $request->all(),
            $docenteId
        );

        return response()->json([
            'message' => 'Evento actualizado correctamente',
            'evento' => $evento
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}

//Operacion GET: Mostrar los eventos que le asignaron al docente
public function mostrarEventosAsignados()
{
    try {

        $eventos = $this->eventoService->mostrarEventosAsignados(
            auth()->id()
        );

        return response()->json([
            'message' => 'Eventos asignados obtenidos correctamente',
            'eventos' => $eventos
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);

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

//Funciones para la gestion de eventos por parte del administrador
//Operacion POST: creacion de un evento general 
public function crearEventoGeneral(Request $request,) {
    $request->validate([
            'titulo' => 'required|string|max:255',
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            // Ubicación opcional
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'radio_permitido' => 'nullable|integer|min:1',
            'direccion_texto' => 'nullable|string|max:255',
        ]);

    try {
        $evento = $this->eventoService->crearEventoGeneral(
        $request->all(),
            auth()->id()
        );

        return response()->json([
            'message' => 'Evento general creado correctamente',
            'evento' => $evento
        ], 201);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}

//Operacion GET: Mostrar eventos creados por el usuario administrador
public function obtenerEventosAdministrador() {

    try {

        $eventos =  $this->eventoService->obtenerEventosAdministrador(
            auth()->id()
        );

        return response()->json([
            'message' => 'Eventos obtenidos correctamente',
            'eventos' => $eventos
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}



//Operacion DELETE: Eliminar un evento indicado con id como referencia

public function eliminarEventoGeneral($eventoId) {

    try {

         $this->eventoService->eliminarEventoGeneral(
            $eventoId,
            auth()->id()
        );

        return response()->json([
            'message' => 'Evento general eliminado correctamente'
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}

// Operacion PUT: Actualizar un evento indicado con id como referencia 

public function actualizarEventoGeneral($eventoId, Request $request,) 
{
    $request->validate([
            'titulo' => 'required|string|max:255',
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            // GPS opcional
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'radio_permitido' => 'nullable|integer|min:1',
            'direccion_texto' => 'nullable|string|max:255',
        ]);

    try {

        $evento = $this->eventoService->actualizarEventoGeneral(
            $eventoId,
            $request->all(),
            auth()->id()
        );

        return response()->json([
            'message' => 'Evento general actualizado correctamente',
            'evento' => $evento
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}

// Operacion POST: Asignar un registrador a un evento

public function asignarRegistradorEvento(Request $request) 
{
    $request->validate([
            'evento_id' => 'required|exists:eventos,id',
            // usuario docente
            'usuario_id' => 'required|exists:usuarios,id',
        ]);

    try {


         $this->eventoService->asignarRegistradorEvento(
            $request->evento_id,
            $request->usuario_id,
            auth()->id()
        );

        return response()->json([
            'message' => 'Docente asignado correctamente'
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}

}
