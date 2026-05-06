<?php

namespace App\Modules\Management\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Alumno;
use App\Models\Administrador;
use Illuminate\Support\Facades\Auth;
use App\Models\Asistencia;
use Exception;

class ManagementService{

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
        ->where('correoinstitucional',$correo)
        ->first();

    if (!$administrador) {
            throw new \Exception("Administrador no encontrado");
        }

    return $administrador;
}
   


    //Futuras funciones a editar o eliminar

     //  Eliminar usuario
    public function eliminarUsuario($id)
    {
        $usuarioAuth = Auth::user();

        if (!$usuarioAuth) {
            throw new \Exception("Usuario no autenticado");
        }

        $usuario = Usuario::find($id);

        if (!$usuario) {
            throw new \Exception("Usuario no encontrado");
        }

        $usuario->delete();

        return true;
    }


//Actualizar usuario
public function actualizarUsuario($id, array $data)
{
    $usuarioAuth = Auth::user();

    if (!$usuarioAuth) {
        throw new \Exception("Usuario no autenticado");
    }

    $usuario = Usuario::find($id);

    if (!$usuario) {
        throw new \Exception("Usuario no encontrado");
    }

    // Solo admin puede modificar usuarios
    if ($usuarioAuth->rol_id !== 1) {
        throw new \Exception("No tienes permisos para modificar usuarios");
    }

  

    //  Limpieza de campos opcionales
    if (!isset($data['numero_nomina'])) {
        unset($data['numero_nomina']);
    }

    if (!isset($data['codigo_institucional'])) {
        unset($data['codigo_institucional']);
    }

    $usuario->update($data);

    return $usuario;
}


   
public function asistenciasPorEvento($eventoId)
{
    $usuario = Auth::user();

    if (!$usuario) {
        throw new \Exception("Usuario no autenticado");
    }

    // Solo admin
    if ($usuario->rol_id !== 1) {
        throw new \Exception("No tienes permisos para ver asistencias globales");
    }

    $asistencias = Asistencia::with(['alumno.usuario'])
        ->where('evento_id', $eventoId)
        ->get();

    if ($asistencias->isEmpty()) {
        throw new \Exception("No hay asistencias para este evento");
    }

    //  Formato limpio 
    return $asistencias->map(function ($asistencia) {
        return [
            'alumno_id' => $asistencia->alumno_id,
            'nombre' => $asistencia->alumno->usuario->nombre ?? '',
            'apellidos' => $asistencia->alumno->usuario->apellidos ?? '',
            'estado' => $asistencia->estado,
            'hora_registro' => $asistencia->hora_registro,
        ];
    });
}
  


}
