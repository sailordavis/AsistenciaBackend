<?php

namespace App\Modules\Report\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Report\Services\ReportService;

use Illuminate\Validation\ValidationException; //Necesario para que el sistema pueda reaccionar a las excepciones disparadas

class ReportController extends Controller
{

//Funciones para la seccion del rol alumno 

//Operacion GET: Mostrar todas las asistencias del alumno en sus clases
public function obtenerAsistenciasClaseAlumno(
    $claseId,
    ReportService $service
) {

    try {

        $asistencias = $service->obtenerAsistenciasClaseAlumno(

            auth()->id(),

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


// Operacion GET: Mostrar asistencias de todos los eventos generales que asistio el alumno 

public function obtenerAsistenciasEventosGenerales(
    ReportService $service
) {

    try {

        $asistencias = $service
            ->obtenerAsistenciasEventosGenerales(
                auth()->id()
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

//Funciones para la seccion del rol de docente 



//Operacion GET: Obtener asistencias de las clases de los docentes con el id del evento



public function obtenerAsistenciasEventoClaseDocente(
    $eventoId,
    ReportService $service
) {

    try {

        $asistencias = $service
            ->obtenerAsistenciasEventoClaseDocente(

                auth()->id(),

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


// Operacion GET: Obtencion de asistencias de eventos generales que le asignaron al docente

public function obtenerAsistenciasEventosGeneralesDocente(
    $eventoId,
    ReportService $service
) {

    try {

        $asistencias = $service
            ->obtenerAsistenciasEventosGeneralesDocente(

                auth()->id(),

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
}

