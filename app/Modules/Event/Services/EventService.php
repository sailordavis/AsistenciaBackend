<?php

namespace App\Modules\Event\Services;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Evento;
use App\Models\Clase;
use App\Models\Docente;
use App\Models\Usuario;
use Exception;

class EventService{

   //Gestion de eventos para los eventos de la clase de los docentes
   //Crear un evento para una clase 
   public function crearEventoClase(array $data, $docenteId)
    {
        // Validar que la clase exista
        $clase = Clase::find($data['clase_id']);

        if (!$clase) {
            throw new Exception('Clase no encontrada');
        }

        // Validar que la clase pertenezca al docente
        if ($clase->docente_id != $docenteId) {
            throw new Exception(
                'No tienes permiso para crear eventos en esta clase'
            );
        }

        //Se obtiene el docente
         $docente = Docente::find($docenteId);

        // Crear evento
        $evento = Evento::create([
            'titulo' => $data['titulo'],
            'fecha' => $data['fecha'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'tipo' => 'clase',
            'clase_id' => $data['clase_id'],
            'creador_id' => $docente->usuario_id,

            // Token QR
            'token_evento' => Str::random(40),

            // Ubicación
            'latitud' => $data['latitud'] ?? null,
            'longitud' => $data['longitud'] ?? null,
            'radio_permitido' =>
                $data['radio_permitido'] ?? null,
            'direccion_texto' =>
                $data['direccion_texto'] ?? null,
        ]);

        return $evento;
    }




//Eventos Concluidos
public function obtenerEventosClaseConcluidos(
    $claseId,
    $docenteId
) {

    // Buscar clase
    $clase = Clase::find($claseId);

    if (!$clase) {

        throw new Exception(
            'Clase no encontrada'
        );
    }

    // Validar pertenencia
    if ($clase->docente_id != $docenteId) {

        throw new Exception(
            'No tienes permiso para ver los eventos de esta clase'
        );
    }

  


// Fecha y hora actual
$ahora = now();

// Obtener eventos concluidos
$eventos = Evento::where(
        'clase_id',
        $claseId
    )

    ->get()

    ->filter(function ($evento) use ($ahora) {

        // Fecha y hora exacta de fin
        $finEvento = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $evento->fecha . ' ' . $evento->hora_fin
        );

        // Mostrar solo eventos ya concluidos
        return $ahora->greaterThan($finEvento);
    })

    ->sortByDesc(function ($evento) {

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $evento->fecha . ' ' . $evento->hora_inicio
        );
    })

    ->values();

return $eventos;
}

//Eventos Clase Activos
public function obtenerEventosClaseActivos(
    $claseId,
    $docenteId
) {

    // Buscar clase
    $clase = Clase::find($claseId);

    if (!$clase) {

        throw new Exception(
            'Clase no encontrada'
        );
    }

    // Validar pertenencia
    if ($clase->docente_id != $docenteId) {

        throw new Exception(
            'No tienes permiso para ver los eventos de esta clase'
        );
    }

   


// Fecha y hora actual
$ahora = now();

// Obtener eventos futuros o en curso
$eventos = Evento::where(
        'clase_id',
        $claseId
    )

    ->get()

    ->filter(function ($evento) use ($ahora) {

        // Fecha y hora exacta de fin
        $finEvento = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $evento->fecha . ' ' . $evento->hora_fin
        );

        // Mostrar:
        // - eventos futuros
        // - eventos en curso
        // Ocultar:
        // - eventos concluidos
        return $ahora->lessThanOrEqualTo($finEvento);
    })

    ->sortBy(function ($evento) {

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $evento->fecha . ' ' . $evento->hora_inicio
        );
    })

    ->values();

return $eventos;
}


// Funcion que busca coincidencias en eventos de una clase
public function buscarEventosClase(
    $claseId,
    $docenteId,
    $titulo,
    bool $concluido
) {

    // Buscar clase
    $clase = Clase::find($claseId);

    if (!$clase) {
        throw new Exception('Clase no encontrada');
    }

    // Validar pertenencia
    if ($clase->docente_id != $docenteId) {

        throw new Exception(
            'No tienes permiso para ver los eventos de esta clase'
        );
    }

    $ahora = Carbon::now();

    // Query base
    $query = Evento::where('clase_id', $claseId)
        ->where('titulo', 'LIKE', "%{$titulo}%");

    // Eventos concluidos
    if ($concluido) {

        $query->whereRaw(
            "TIMESTAMP(fecha, hora_fin) < ?",
            [$ahora]
        );

    } else {

        // Eventos futuros o en curso
        $query->whereRaw(
            "TIMESTAMP(fecha, hora_fin) >= ?",
            [$ahora]
        );
    }

    $eventos = $query
        ->orderBy('fecha', 'desc')
        ->orderBy('hora_inicio', 'desc')
        ->get();

    return $eventos;
}


 //Eliminar un evento de una clase en especifico
 public function eliminarEventoClase($eventoId, $docenteId)
{
    // Buscar evento
    $evento = Evento::find($eventoId);

    if (!$evento) {
        throw new Exception('Evento no encontrado');
    }
    //Se obtiene el docente
    $docente = Docente::find($docenteId);

    // Validar que el evento pertenezca al docente
    if ($evento->creador_id != $docente->usuario_id) {

        throw new Exception(
            'No tienes permiso para eliminar este evento'
        );
    }
    

    // Eliminar evento
    // Las relaciones con cascade también se eliminarán
    $evento->delete();

    return true;
}



//Mostrar Eventos asignados al docente
public function mostrarEventosAsignados($usuarioId)
{
    // Validar que exista docente
    $docente = Docente::where('usuario_id', $usuarioId)
        ->first();

    if (!$docente) {
        throw new Exception('Docente no encontrado');
    }


// Obtener fecha y hora actual
$ahora = now();

// Obtener eventos asignados que aún no concluyen
$eventos = Evento::whereHas(
    'registradores',
    function ($query) use ($usuarioId) {

        $query->where(
            'usuarios.id',
            $usuarioId
        );
    }
)
->with([
    'clase',
    'usuario'
])
->orderBy('fecha', 'asc')
->orderBy('hora_inicio', 'asc')
->get()
->filter(function ($evento) use ($ahora) {

    // Fecha y hora exacta de inicio
    $inicioEvento = Carbon::createFromFormat(
        'Y-m-d H:i:s',
        $evento->fecha . ' ' . $evento->hora_inicio
    );

    // Fecha y hora exacta de fin
    $finEvento = Carbon::createFromFormat(
        'Y-m-d H:i:s',
        $evento->fecha . ' ' . $evento->hora_fin
    );

    // Mostrar:
    // - eventos futuros
    // - eventos en curso
    // Ocultar:
    // - eventos terminados
    return $ahora->lessThanOrEqualTo($finEvento);
})
->values();

    return $eventos;
}

//Funciones para la gestion de eventos por parte del administrador
//Funcion para la creacion de un evento general 
public function crearEventoGeneral( array $data, $usuarioId
) {
    // Crear evento
    $evento = Evento::create([
        'titulo' => $data['titulo'],
        'fecha' => $data['fecha'],
        'hora_inicio' => $data['hora_inicio'],
        'hora_fin' => $data['hora_fin'],
        // El tipo lo manda el admin
        'tipo' => 'general',
        // Evento general NO tiene clase
        'clase_id' => null,
        'creador_id' => $usuarioId,
        // Token QR
        'token_evento' => Str::random(40),
        // GPS
        'latitud' => $data['latitud'] ?? null,
        'longitud' => $data['longitud'] ?? null,
        'radio_permitido' =>
            $data['radio_permitido'] ?? null,
        'direccion_texto' =>
            $data['direccion_texto'] ?? null,
    ]);

    return $evento;
}

//Funcion que obtiene los eventos creados por el administrador
public function obtenerEventosAdministrador($usuarioId)
{
    $eventos = Evento::where(
        'creador_id',
        $usuarioId
    )
    ->where(
        'tipo',
        'general'
    )
    ->orderBy('fecha', 'asc')
    ->orderBy('hora_inicio', 'asc')
    ->get();

    return $eventos;
}


//Funcion para eliminar un evento general con el id obtenido
public function eliminarEventoGeneral($eventoId,$usuarioId ) {

    // Buscar evento
    $evento = Evento::find($eventoId);

    if (!$evento) {
        throw new Exception('Evento no encontrado');
    }

    // Validar que sea evento general
    if ($evento->tipo !== 'general') {

        throw new Exception(
            'El evento no es de tipo general'
        );
    }

    // Validar dueño
    if ($evento->creador_id != $usuarioId) {

        throw new Exception(
            'No tienes permiso para eliminar este evento'
        );
    }

    

    // Eliminar evento
    $evento->delete();

    return true;
}


//Funcion para actualizar un evento general
public function actualizarEventoGeneral(
    $eventoId,
    array $data,
    $usuarioId
) {

    // Buscar evento
    $evento = Evento::find($eventoId);

    if (!$evento) {
        throw new Exception('Evento no encontrado');
    }

    // Validar que sea general
    if ($evento->tipo !== 'general') {

        throw new Exception(
            'El evento no es de tipo general'
        );
    }

    // Validar ownership
    if ($evento->creador_id != $usuarioId) {

        throw new Exception(
            'No tienes permiso para modificar este evento'
        );
    }

    // Validar que NO haya iniciado
    $inicioEvento = Carbon::parse(
        $evento->fecha . ' ' . $evento->hora_inicio
    );

    if (now()->greaterThanOrEqualTo($inicioEvento)) {

        throw new Exception(
            'No puedes modificar un evento que ya inició'
        );
    }

    // Actualizar evento
    $evento->update([

        'titulo' => $data['titulo'],
        'fecha' => $data['fecha'],
        'hora_inicio' => $data['hora_inicio'],
        'hora_fin' => $data['hora_fin'],
        // Mantener tipo general
        'tipo' => 'general',
        // clase_id siempre null
        'clase_id' => null,
        // GPS
        'latitud' =>
            $data['latitud'] ?? null,
        'longitud' =>
            $data['longitud'] ?? null,
        'radio_permitido' =>
            $data['radio_permitido'] ?? null,
        'direccion_texto' =>
            $data['direccion_texto'] ?? null,
    ]);

    return $evento;
}
//Funcion para asignar un registrador a un evento
public function asignarRegistradorEvento(
    $eventoId,
    $usuarioDocenteId,
    $usuarioAdminId
) {

    // Buscar evento
    $evento = Evento::find($eventoId);

    if (!$evento) {
        throw new Exception('Evento no encontrado');
    }

    // Validar que sea evento general
    if ($evento->tipo !== 'general') {

        throw new Exception(
            'Solo se pueden asignar registradores a eventos generales'
        );
    }

    // Validar ownership del administrador
    if ($evento->creador_id != $usuarioAdminId) {

        throw new Exception(
            'No tienes permiso para asignar docentes a este evento'
        );
    }

    // Validar usuario
    $usuario = Usuario::find($usuarioDocenteId);

    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }

    // Validar que el usuario sea docente
    $docente = Docente::where(
        'usuario_id',
        $usuarioDocenteId
    )->first();

    if (!$docente) {

        throw new Exception(
            'El usuario no pertenece a un docente'
        );
    }

    // Hora actual
    $ahora = now();

    // Fecha y hora de inicio del evento
     $inicioEvento = Carbon::createFromFormat(
    'Y-m-d H:i:s',
    $evento->fecha . ' ' . $evento->hora_inicio
    );

     // Validar que el evento aún no haya iniciado
      if ($ahora->greaterThanOrEqualTo($inicioEvento)) {

    throw new Exception(
        'No puedes asignar un docente a un evento que ya inició o está en curso'
    );
}

    // Validar que no esté asignado previamente
    $yaAsignado = $evento->registradores()
        ->where('usuarios.id', $usuarioDocenteId)
        ->exists();

    if ($yaAsignado) {

        throw new Exception(
            'El docente ya está asignado al evento'
        );
    }

    // Asignar docente
    $evento->registradores()->attach(
        $usuarioDocenteId
    );

    return true;
}



    


}