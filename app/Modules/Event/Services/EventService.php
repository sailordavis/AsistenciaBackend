<?php

namespace App\Modules\Event\Services;


use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Evento;
use Exception;

class EventService{

   public function crearEvento(array $data)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            throw new \Exception("Usuario no autenticado");
        }

        //  Validaciones de negocio

        // Fecha no puede ser en el pasado
        if (Carbon::parse($data['fecha'])->isPast()) {
            throw new \Exception("No puedes crear eventos en fechas pasadas");
        }

        // Hora fin mayor que hora inicio
        if ($data['hora_fin'] <= $data['hora_inicio']) {
            throw new \Exception("La hora fin debe ser mayor a la hora inicio");
        }


        //  Asignar creador automáticamente
        $data['creador_id'] = $usuario->id;

        //  Crear evento
        $evento = Evento::create($data);

        return $evento;
    }

     //  Mostrar un evento
    public function mostrarEvento($titulo)
    {
        
        $evento = Evento::where('titulo', $titulo)->first();

        if (!$evento) {
            throw new \Exception("Evento no encontrado");
        }

        return $evento;
    }

    //  Mostrar todos los eventos
      public function listarEventos()
   {
    $eventos = Evento::orderBy('fecha', 'asc')
                     ->orderBy('hora_inicio', 'asc')
                     ->get();

    return $eventos;
   }


       //  Editar / actualizar evento
    public function actualizarEvento($titulo, array $data)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            throw new \Exception("Usuario no autenticado");
        }
        $evento = Evento::where('titulo', $titulo)->first();

        if (!$evento) {
            throw new \Exception("Evento no encontrado");
        }

        //  validar que solo el creador pueda editar
        if ($evento->creador_id !== $usuario->id) {
            throw new \Exception("No tienes permiso para editar este evento");
        }

        // Validaciones de negocio
        if (isset($data['fecha']) && Carbon::parse($data['fecha'])->isPast()) {
            throw new \Exception("No puedes poner fechas pasadas");
        }

        if (isset($data['hora_inicio']) && isset($data['hora_fin'])) {
            if ($data['hora_fin'] <= $data['hora_inicio']) {
                throw new \Exception("La hora fin debe ser mayor a la hora inicio");
            }
        }

        $evento->update($data);

        return $evento;
    }


    //  Eliminar un  evento
    public function eliminarEvento($titulo)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            throw new \Exception("Usuario no autenticado");
        }

        $evento = Evento::where('titulo', $titulo)->first();

        if (!$evento) {
            throw new \Exception("Evento no encontrado");
        }

        // Opcional: validar propietario
        if ($evento->creador_id !== $usuario->id) {
            throw new \Exception("No tienes permiso para eliminar este evento");
        }

        $evento->delete();

        return true;
    }


}