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




     
     //  Mostrar eventos que pertenecen a una clase en particular 
    public function obtenerEventosClase($claseId, $docenteId)
{
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

    // Obtener eventos
    $eventos = Evento::where('clase_id', $claseId)
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

 //Actualizar o modificar un evento de una clase en especifico
 public function actualizarEventoClase($eventoId, array $data, $docenteId)
{
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

    // validar que NO haya iniciado el evento
    $inicioEvento = Carbon::parse(
        $evento->fecha . ' ' . $evento->hora_inicio
    );

    if (now()->greaterThanOrEqualTo($inicioEvento)) {
        throw new Exception('No puedes modificar un evento que ya inició');
    }

    // actualizar
    $evento->update([
        'titulo' => $data['titulo'],
        'fecha' => $data['fecha'],
        'hora_inicio' => $data['hora_inicio'],
        'hora_fin' => $data['hora_fin'],

        'latitud' => $data['latitud'] ?? $evento->latitud,
        'longitud' => $data['longitud'] ?? $evento->longitud,
        'radio_permitido' => $data['radio_permitido'] ?? $evento->radio_permitido,
        'direccion_texto' => $data['direccion_texto'] ?? $evento->direccion_texto,
    ]);

    return $evento;
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

    // Obtener eventos asignados que aún no terminan
    $eventos = Evento::whereHas(
        'registradores',
        function ($query) use ($usuarioId) {

            $query->where(
                'usuarios.id',
                $usuarioId
            );
        }
    )
    ->where(function ($query) use ($ahora) {

        // Eventos futuros
        $query->where('fecha', '>', $ahora->toDateString())

        // O eventos de hoy que aún no terminan
        ->orWhere(function ($subQuery) use ($ahora) {

            $subQuery->where(
                'fecha',
                $ahora->toDateString()
            )
            ->where(
                'hora_fin',
                '>=',
                $ahora->format('H:i:s')
            );
        });
    })
    ->with([
        'clase',
        'usuario'
    ])
    ->orderBy('fecha', 'asc')
    ->orderBy('hora_inicio', 'asc')
    ->get();

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

    // Validar que el evento no haya iniciado
    $inicioEvento = Carbon::parse(
        $evento->fecha . ' ' . $evento->hora_inicio
    );

    if (now()->greaterThanOrEqualTo($inicioEvento)) {

        throw new Exception(
            'No puedes asignar docentes a un evento que ya inició'
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