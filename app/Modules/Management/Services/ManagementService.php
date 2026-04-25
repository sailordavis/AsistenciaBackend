<?php

namespace App\Modules\Management\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use App\Models\Asistencia;
use Exception;

class ManagementService{

 // Listar usuarios
    public function listarUsuarios()
    {
        return Usuario::orderBy('created_at', 'desc')->get();
    }


//  Mostrar usuario
    public function mostrarUsuario($correoInstitucional)
    {
        
        $usuario = Usuario::where('correoInstitucional', $correoInstitucional)->first();

        if (!$usuario) {
            throw new \Exception("Usuario no encontrado");
        }

        return $usuario;
    }

   

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
