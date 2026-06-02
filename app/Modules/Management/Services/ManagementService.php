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
use Carbon\Carbon;
use Exception;

class ManagementService{

  //Gestion de usuarios

 // Funcion para listar todos los usuarios 
    public function listarUsuarios()
    {
     return Usuario::with('rol')
    ->where('rol_id', '!=', 1)
    ->orderBy('created_at', 'desc')
    ->get();
    }


// Mostrar usuarios que coincidan con el correo proporcionado
public function mostrarUsuarios($correo)
{
    $usuarios = Usuario::where(
        'correoinstitucional',
        'LIKE',
        "%$correo%"
    )
    ->where('rol_id', '!=', 1)
    ->get();

    if ($usuarios->isEmpty()) {

        throw new \Exception("No se encontraron coincidencias");

    }

    return $usuarios;
}

   // Mostrar docentes que coincidan con la nómina proporcionada
public function mostrarDocentes($correo)
{
    $docentes = Usuario::with('docente') 
     ->whereHas('docente', function ($query) use ($correo){
       $query->where(
            'correoinstitucional',
            'LIKE',
            "%$correo%"
        );
     })
    ->get();

    if ($docentes->isEmpty()) {

        throw new \Exception("No se encontraron coincidencias");

    }

    return $docentes;
}

//Mostrar alumno con codigo proporcionado via url
public function mostrarAlumnos($correo)
{
    $alumnos = Usuario::with('alumno')

    ->whereHas('alumno', function ($query) use ($correo) {

        $query->where(
            'correoinstitucional',
            'LIKE',
            "%$correo%"
        );

    })

    ->get();

    if ($alumnos->isEmpty()) {

        throw new \Exception("No se encontraron coincidencias");

    }

    return $alumnos;
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

//Mostrar las clases a las que pertenece el alumno 
public function obtenerClasesAlumno($alumnoId)
{
    // Buscar alumno
    $alumno = Alumno::find($alumnoId);

    if (!$alumno) {
        throw new Exception('Alumno no encontrado');
    }

    // Obtener solo clases con docente y usuario del docente
    $clases = $alumno->clases()
        ->with([
            'docente.usuario'
        ])
        ->orderBy('nombre', 'asc')
        ->get();

    return [
        'clases' => $clases
    ];
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
    function ($query) use ($usuarioId) {

        $query->where(
            'usuarios.id',
            $usuarioId
        );
    }
)
->get()
->filter(function ($evento) use ($ahora) {

    // Construir fecha y hora exacta de inicio
    $inicioEvento = Carbon::createFromFormat(
        'Y-m-d H:i:s',
        $evento->fecha . ' ' . $evento->hora_inicio
    );

    // Construir fecha y hora exacta de fin
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
   




//Funciones para la visualizacion global de asistencias

//Funcion para obtener las asistencias globales de eventos 
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


public function obtenerAsistenciasClaseAlumno(
    $alumnoId,
    $claseId
) {

    // Buscar alumno
    $alumno = Alumno::find($alumnoId);

    if (!$alumno) {

        throw new Exception(
            'Alumno no encontrado'
        );
    }

    // Buscar clase
    $clase = Clase::find($claseId);

    if (!$clase) {

        throw new Exception(
            'Clase no encontrada'
        );
    }

    // Validar que el alumno pertenezca a la clase
    $perteneceClase = $alumno->clases()
        ->where(
            'clases.id',
            $claseId
        )
        ->exists();

    if (!$perteneceClase) {

        throw new Exception(
            'El alumno no pertenece a esta clase'
        );
    }

    // Obtener asistencias
    $asistencias = Asistencia::with([

        'evento',
        'evento.clase',
        'evento.usuario',
        'alumno.usuario'

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

    return [

        'alumno' => $alumno,

        'clase' => $clase,

        'asistencias' => $asistencias

    ];
}

//Obtener asistencias de alumnos de eventos generales 
public function obtenerAsistenciasEventosGeneralesAlumno(
    $alumnoId
) {

    // Buscar alumno
    $alumno = Alumno::with([
        'usuario'
    ])->find($alumnoId);

    if (!$alumno) {

        throw new Exception(
            'Alumno no encontrado'
        );
    }

    // Obtener asistencias de eventos generales
    $asistencias = Asistencia::with([

        'evento',
        'evento.usuario',
        'alumno.usuario'

    ])
    ->where(
        'alumno_id',
        $alumno->id
    )
    ->whereHas('evento', function ($query) {

        $query->whereNull(
            'clase_id'
        );
    })
    ->orderBy(
        'hora_registro',
        'desc'
    )
    ->get();

    return [

        'alumno' => $alumno,

        'asistencias' => $asistencias

    ];
}


  


}
