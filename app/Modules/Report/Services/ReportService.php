<?php

namespace App\Modules\Report\Services;
use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Docente;
use App\Models\Evento;

use Exception;

class ReportService{

//Funciones para la seccion de alumno

//Funcion que muestra todas las asistencias del alumno en sus clases
public function obtenerAsistenciasClaseAlumno(
    $usuarioId,
    $claseId
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

    // Validar que pertenezca a la clase
    $perteneceClase = $alumno->clases()
        ->where(
            'clases.id',
            $claseId
        )
        ->exists();

    if (!$perteneceClase) {

        throw new Exception(
            'No perteneces a esta clase'
        );
    }

    // Obtener asistencias
    $asistencias = Asistencia::with([

        'evento',
        'evento.usuario'

    ])
    ->where(
        'alumno_id',
        $alumno->id
    )
    ->whereHas('evento', function ($query) use ($claseId) {

        $query->where(
            'clase_id',
            $claseId
        );
    })
    ->orderBy(
        'hora_registro',
        'desc'
    )
    ->get();

    return $asistencias;
}

//Funcion que muestra todas las asistencias del alumno en eventos generales
public function obtenerAsistenciasEventosGenerales(
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

    // Obtener asistencias de eventos generales
    $asistencias = Asistencia::with([

        'evento',
        'evento.usuario'

    ])
    ->where(
        'alumno_id',
        $alumno->id
    )
    ->whereHas('evento', function ($query) {

        $query->whereNull('clase_id');
    })
    ->orderBy(
        'hora_registro',
        'desc'
    )
    ->get();

    return $asistencias;
}

//Funciones para la seccion de docente



//Funcion para obtener la asistencia de un evento de una clase
public function obtenerAsistenciasEventoClaseDocente(
    $usuarioId,
    $eventoId
) {

    // Buscar docente autenticado
    $docente = Docente::where(
        'usuario_id',
        $usuarioId
    )->first();

    if (!$docente) {

        throw new Exception(
            'Docente no encontrado'
        );
    }

    // Buscar evento
    $evento = Evento::with([
        'clase'
    ])->find($eventoId);

    if (!$evento) {

        throw new Exception(
            'Evento no encontrado'
        );
    }

    // Validar que sea evento de clase
    if ($evento->clase_id === null) {

        throw new Exception(
            'El evento no pertenece a una clase'
        );
    }

    // Validar ownership de la clase
    if ($evento->clase->docente_id != $docente->id) {

        throw new Exception(
            'No tienes permiso sobre este evento'
        );
    }

    // Obtener asistencias
    $asistencias = Asistencia::with([

        'alumno.usuario',
        'evento'

    ])
    ->where(
        'evento_id',
        $evento->id
    )
    ->orderBy(
        'hora_registro',
        'desc'
    )
    ->get();

    return $asistencias;
}




//Funcion que muestra las asistencias de eventos generales que le asignaron al docente
public function obtenerAsistenciasEventosGeneralesDocente(
    $usuarioId,
    $eventoId
) {

    // Buscar docente autenticado
    $docente = Docente::where(
        'usuario_id',
        $usuarioId
    )->first();

    if (!$docente) {

        throw new Exception(
            'Docente no encontrado'
        );
    }

    // Buscar evento
    $evento = Evento::with([
        'registradores'
    ])->find($eventoId);

    if (!$evento) {

        throw new Exception(
            'Evento no encontrado'
        );
    }

    // Validar que sea evento general
    if ($evento->clase_id !== null) {

        throw new Exception(
            'El evento no es general'
        );
    }

    // Validar que el docente esté asignado
    $asignado = $evento->registradores()
        ->where(
            'usuarios.id',
            $usuarioId
        )
        ->exists();

    if (!$asignado) {

        throw new Exception(
            'No tienes permiso para ver este evento'
        );
    }

    // Obtener asistencias
    $asistencias = Asistencia::with([

        'alumno.usuario',
        'evento'

    ])
    ->where(
        'evento_id',
        $evento->id
    )
    ->orderBy(
        'hora_registro',
        'desc'
    )
    ->get();

    return $asistencias;
}

}