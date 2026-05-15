<?php

namespace App\Modules\Management\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Alumno;
use App\Models\Administrador;
use App\Models\Clase;
use App\Models\Evento;
use Illuminate\Support\Facades\Auth;
use App\Models\Asistencia;
use Exception;

class ManagementService{

  //Gestion de usuarios

 // Funcion para listar todos los usuarios 
    public function listarUsuarios()
    {
        return Usuario::with('rol')
        ->orderBy('created_at', 'desc')
        ->get();
    }


//  Mostrar usuario con correo proporcionado via url 
    public function mostrarUsuario($correo)
    {
        $usuario = Usuario::where('correoinstitucional', $correo)
        ->first();

    if (!$usuario) {
            throw new \Exception("Usuario no encontrado");
        }

    return $usuario;
    }

    //Mostrar docente con nomina proporcionado via url 
    public function mostrarDocente($nomina)
{
    $docente = Docente::with('usuario') // para traer nombre, correo, etc.
        ->where('numero_nomina',$nomina)
        ->first();

    if (!$docente) {
            throw new \Exception("Docente no encontrado");
        }
    
        return $docente;
}

//Mostrar alumno con codigo proporcionado via url
public function mostrarAlumno($codigo)
{
    $alumno = Alumno::with('usuario') // trae nombre, correo, etc.
        ->where('codigo_institucional', $codigo)
        ->first();

    if (!$alumno) {
            throw new \Exception("Alumno no encontrado");
        }

    return $alumno;
}

//Mostrar administrador con correo proporcionado via url
public function mostrarAdministrador($correo)
{
     $administrador = Administrador::with('usuario')
    ->whereHas('usuario', function ($query) use ($correo) {
        $query->where('correoinstitucional', $correo);
    })
    ->first();

    if (!$administrador) {
            throw new \Exception("Administrador no encontrado");
        }

    return $administrador;
}

//Funciones para la visualizacion gobal de clases

//Mostrar  todas las clases del docente seleccionado
    public function mostrarClasesDocente($docenteId)
    {
    // Retornar clases propias del docente
    return Clase::where('docente_id', $docenteId)
        ->orderBy('created_at', 'desc')
        ->get();
    }


      //Mostrar listas de alumnos por clase

   public function mostrarAlumnosClase($claseId)
{
    $clase = Clase::with('alumnos.usuario')
        ->where('id', $claseId)
        ->first();

    if (!$clase) {
        throw new \Exception("Clase no encontrada");
    }

    return $clase->alumnos;
}

//Funciones para la visualizacion global de eventos
//Funcion que obtiene los eventos generales creados junto los datos de su creador 
public function obtenerEventosGenerales()
{
    $eventos = Evento::where(
        'tipo',
        'general'
    )
    ->with([
        'usuario'
    ])
    ->orderBy('fecha', 'asc')
    ->orderBy('hora_inicio', 'asc')
    ->get();

    return $eventos;
}

    //  Mostrar eventos que pertenecen a una clase en particular 
    public function obtenerEventosClase($claseId)
{
    // Buscar clase
    $clase = Clase::find($claseId);

    if (!$clase) {
        throw new Exception('Clase no encontrada');
    }

    

    // Obtener eventos
    $eventos = Evento::where('clase_id', $claseId)
        ->orderBy('fecha', 'desc')
        ->orderBy('hora_inicio', 'desc')
        ->get();

    return $eventos;
}

//Mostrar Eventos asignados al docente
public function mostrarEventosAsignados($docenteId)
{
    // Validar que exista docente
    $docente = Docente::find($docenteId);

    if (!$docente) {
        throw new Exception('Docente no encontrado');
    }
    //Obtener el id usuario del docente
    $usuarioId = $docente->usuario_id;


    // Obtener fecha y hora actual
    $ahora = now();

    // Obtener eventos asignados que aún no terminan
    $eventos = Evento::whereHas(
        'registradores',
        function ($query) use ($usuarioId ) {

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
   




//Funciones para la visualizacion global de asistencias
public function obtenerAsistenciasEvento(
    $eventoId
) {

    // Buscar evento
    $evento = Evento::find($eventoId);

    if (!$evento) {

        throw new Exception(
            'Evento no encontrado'
        );
    }

    // Obtener asistencias
    $asistencias = Asistencia::with([

        'alumno.usuario',
        'evento',
        'evento.clase',
        'evento.usuario'

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
