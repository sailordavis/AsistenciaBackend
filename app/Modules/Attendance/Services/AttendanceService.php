<?php

namespace App\Modules\Attendance\Services;
use App\Models\Alumno;
use App\Models\Evento;
use App\Models\Asistencia;
use Carbon\Carbon;

use Exception;

class AttendanceService{

 //Funcion  de registro de asistencia por parte del usuario alumno

public function registrarAsistenciaQR(
    $tokenEvento,
    $latitudUsuario = null,
    $longitudUsuario = null,
    $usuarioId
) {

    // Buscar alumno autenticado
    $alumno = Alumno::where(
        'usuario_id',
        $usuarioId
    )->first();

    if (!$alumno) {

        throw new Exception(
            'Alumno no encontrado'
        );
    }

    // Buscar evento por token QR
    $evento = Evento::where(
        'token_evento',
        $tokenEvento
    )->first();

    if (!$evento) {

        throw new Exception(
            'Evento no encontrado'
        );
    }

    // Validar que el evento este activo
    $ahora = now();

    $inicioEvento = Carbon::parse(
        $evento->fecha . ' ' . $evento->hora_inicio
    );

    $finEvento = Carbon::parse(
        $evento->fecha . ' ' . $evento->hora_fin
    );

    if (
        $ahora->lt($inicioEvento) ||
        $ahora->gt($finEvento)
    ) {

        throw new Exception(
            'El evento no está disponible para registrar asistencia'
        );
    }

    // Validar asistencia duplicada
    $asistenciaExistente = Asistencia::where(
        'evento_id',
        $evento->id
    )
    ->where(
        'alumno_id',
        $alumno->id
    )
    ->exists();

    if ($asistenciaExistente) {

        throw new Exception(
            'La asistencia ya fue registrada'
        );
    }

   //Validar tipo de evento

    // EVENTO DE CLASE
    if ($evento->clase_id !== null) {

        // Validar que el alumno pertenezca a la clase
        $perteneceClase = $alumno->clases()
            ->where(
                'clases.id',
                $evento->clase_id
            )
            ->exists();

        if (!$perteneceClase) {

            throw new Exception(
                'El alumno no pertenece a esta clase'
            );
        }
    }

//Validar ubicacion 

if (
    $evento->latitud !== null &&
    $evento->longitud !== null &&
    $evento->radio_permitido !== null
) {

    // Validar que frontend mande ubicación
    if (
        $latitudUsuario === null ||
        $longitudUsuario === null
    ) {

        throw new Exception(
            'Debes compartir tu ubicación para registrar asistencia'
        );
    }

    // Calcular distancia en metros
    $distancia = $this->calcularDistanciaMetros(

        $evento->latitud,
        $evento->longitud,

        $latitudUsuario,
        $longitudUsuario
    );

    // Validar radio permitido
    if ($distancia > $evento->radio_permitido) {

        throw new Exception(
            'Te encuentras fuera del rango permitido'
        );
    }
}

    

    // Registrar asistencia
    $asistencia = Asistencia::create([

        'evento_id' => $evento->id,

        'alumno_id' => $alumno->id,

        'estado' => 'presente',

        'hora_registro' => now(),
    ]);

    return $asistencia;
}

//Funciones para el registro de asistencias por parte del docente

//Busqueda del alumno en base de la informacion recibida
public function buscarAlumnos(
    $eventoId,
    $busqueda
) {
    
    // Buscar evento
    $evento = Evento::find($eventoId);
    

    //Evento de clase

    if ($evento->clase_id !== null) {

        $alumnos = Alumno::with([
            'usuario'
        ])
        ->whereHas('clases', function ($query) use ($evento) {

            $query->where(
                'clases.id',
                $evento->clase_id
            );
        })
        ->where(function ($query) use ($busqueda) {

            // Buscar por código institucional
            $query->where(
                'codigo_institucional',
                'ILIKE',
                "%{$busqueda}%"
            )

            // Buscar en usuario
            ->orWhereHas('usuario', function ($usuarioQuery) use ($busqueda) {

                $usuarioQuery->where(
                    'nombre',
                    'ILIKE',
                    "%{$busqueda}%"
                )

                ->orWhere(
                    'apellidos',
                    'ILIKE',
                    "%{$busqueda}%"
                )

                ->orWhere(
                    'correoinstitucional',
                    'ILIKE',
                    "%{$busqueda}%"
                );
            });
        })
        ->get();

        return $alumnos;
    }

    //Evento general 

    $alumnos = Alumno::with([
        'usuario'
    ])
    ->where(function ($query) use ($busqueda) {

        $query->where(
            'codigo_institucional',
            'ILIKE',
            "%{$busqueda}%"
        )

        ->orWhereHas('usuario', function ($usuarioQuery) use ($busqueda) {

            $usuarioQuery->where(
                'nombre',
                'ILIKE',
                "%{$busqueda}%"
            )

            ->orWhere(
                'apellidos',
                'ILIKE',
                "%{$busqueda}%"
            )

            ->orWhere(
                'correoinstitucional',
                'ILIKE',
                "%{$busqueda}%"
            );
        });
    })
    ->get();

    return $alumnos;
}

//Funcion para el registro de asistencia de forma manual por parte del docente
public function registrarAsistenciaManual(
    $eventoId,
    $alumnoId,
    $usuarioDocenteId,
    $latitudDocente = null,
    $longitudDocente = null
) {

    // Buscar evento
    $evento = Evento::find($eventoId);

    if (!$evento) {

        throw new Exception(
            'Evento no encontrado'
        );
    }

    // Buscar alumno
    $alumno = Alumno::find($alumnoId);

    if (!$alumno) {

        throw new Exception(
            'Alumno no encontrado'
        );
    }

    //Validar evento activo

    $ahora = now();

    $inicioEvento = Carbon::parse(
        $evento->fecha . ' ' . $evento->hora_inicio
    );

    $finEvento = Carbon::parse(
        $evento->fecha . ' ' . $evento->hora_fin
    );

    if (
        $ahora->lt($inicioEvento) ||
        $ahora->gt($finEvento)
    ) {

        throw new Exception(
            'El evento no está disponible'
        );
    }

   //Validar ubicacion del docente

    if (
        $evento->latitud !== null &&
        $evento->longitud !== null &&
        $evento->radio_permitido !== null
    ) {

        if (
            $latitudDocente === null ||
            $longitudDocente === null
        ) {

            throw new Exception(
                'Debes compartir tu ubicación'
            );
        }

        $distancia = $this->calcularDistanciaMetros(

            $evento->latitud,
            $evento->longitud,

            $latitudDocente,
            $longitudDocente
        );

        if ($distancia > $evento->radio_permitido) {

            throw new Exception(
                'Te encuentras fuera del rango permitido'
            );
        }
    }

    //Validar asistencia duplicada

    $asistenciaExistente = Asistencia::where(
        'evento_id',
        $evento->id
    )
    ->where(
        'alumno_id',
        $alumno->id
    )
    ->exists();

    if ($asistenciaExistente) {

        throw new Exception(
            'La asistencia ya fue registrada'
        );
    }

    //Registrar asistencia

    $asistencia = Asistencia::create([

        'evento_id' => $evento->id,

        'alumno_id' => $alumno->id,

        'estado' => 'presente',

        'hora_registro' => now(),
    ]);

    return $asistencia;
}

//Funcion auxiliar 
private function calcularDistanciaMetros(
    $lat1,
    $lon1,
    $lat2,
    $lon2
) {

    $radioTierra = 6371000;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a =
        sin($dLat / 2) * sin($dLat / 2) +

        cos(deg2rad($lat1)) *
        cos(deg2rad($lat2)) *

        sin($dLon / 2) *
        sin($dLon / 2);

    $c = 2 * atan2(
        sqrt($a),
        sqrt(1 - $a)
    );

    return $radioTierra * $c;
}


}