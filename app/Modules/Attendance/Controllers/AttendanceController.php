<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Attendance\Services\AttendanceService;

use Illuminate\Validation\ValidationException; //Necesario para que el sistema pueda reaccionar a las excepciones disparadas

class AttendanceController extends Controller
{
//Operacion de registro de asistencia por parte del usuario alumno
//Operacion POST: Registro de asistencia propia por parte del alumno 
public function registrarAsistenciaQR(
    Request $request,
    AttendanceService $service
) {

    try {

        $request->validate([

            'token_evento' => 'required|string',
             'latitud' => 'nullable|numeric',
             'longitud' => 'nullable|numeric'
        ]);

        $asistencia = $service->registrarAsistenciaQR(
            $request->token_evento,
            $request->latitud,
            $request->longitud,
            auth()->id()
        );

        return response()->json([
            'message' => 'Asistencia registrada correctamente',
            'asistencia' => $asistencia
        ], 201);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}


//Operacion de registro de asistencia por parte del docente de forma manual
//Operacion POST: Busqueda del alumno para la verificacion de asistencia 

public function buscarAlumnos(
    Request $request,
    AttendanceService $service
) {

    $request->validate([
            'evento_id' =>'required|integer|exists:eventos,id',
            'busqueda' => 'required|string|max:255'
            ]);

    try {

        $alumnos = $service->buscarAlumnos(
            $request->evento_id,
            $request->busqueda
        );

       return response()->json([
            'success' => true,
            'data' => $alumnos
        ], 200);

    } catch (\Exception $e) {

        return response()->json([

            'message' => $e->getMessage()

        ], 400);
    }
}


//Operacion POST: Registro de asistencia manual por parte del docente

public function registrarAsistenciaManual(
    Request $request,
    AttendanceService $service
) {


    $request->validate([

            'evento_id' => 'required|exists:eventos,id',

            'alumno_id' => 'required|exists:alumnos,id',

            // Ubicación del docente
            'latitud' => 'nullable|numeric',

            'longitud' => 'nullable|numeric',
        ]);

    try {

        

        $asistencia = $service->registrarAsistenciaManual(

            $request->evento_id,

            $request->alumno_id,

            auth()->id(),

            $request->latitud,

            $request->longitud
        );

        return response()->json([

            'message' => 'Asistencia manual registrada correctamente',

            'asistencia' => $asistencia

        ], 201);

    } catch (\Exception $e) {

        return response()->json([

            'message' => $e->getMessage()

        ], 400);
    }
}


}