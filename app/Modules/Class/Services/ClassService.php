<?php

namespace App\Modules\Class\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\Clase;
use Exception;

class ClassService
{
    //  Crear clase
    public function crearClase(array $data)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            throw new \Exception("Usuario no autenticado");
        }

        // Asignar docente automáticamente
        $data['docente_id'] = $usuario->id;

        return Clase::create($data);
    }

    //  Listar todas las clases
    public function listarClases()
    {
        return Clase::orderBy('created_at', 'desc')->get();
    }

    //  Mostrar una clase
    public function mostrarClase($nombre)
    {
         $clase = Clase::where('nombre', $nombre)->first();

        if (!$clase) {
            throw new \Exception("Clase no encontrada");
        }

        return $clase;
    }

    //  Actualizar clase
    public function actualizarClase($nombre, array $data)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            throw new \Exception("Usuario no autenticado");
        }

        $clase = Clase::where('nombre', $nombre)->first();

        if (!$clase) {
            throw new \Exception("Clase no encontrada");
        }

        // Validar que solo el docente pueda editar
        if ($clase->docente_id !== $usuario->id) {
            throw new \Exception("No tienes permiso para editar esta clase");
        }

        $clase->update($data);

        return $clase;
    }

    //  Eliminar clase
    public function eliminarClase($nombre)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            throw new \Exception("Usuario no autenticado");
        }

        $clase = Clase::where('nombre', $nombre)->first();

        if (!$clase) {
            throw new \Exception("Clase no encontrada");
        }

        // Validar propietario
        if ($clase->docente_id !== $usuario->id) {
            throw new \Exception("No tienes permiso para eliminar esta clase");
        }

        $clase->delete();

        return true;
    }

     public function listaAlumnosPorClase($nombre)
{
    // Buscar por nombre 
    $clase = Clase::where('nombre', $nombre)->first();

    if (!$clase) {
        throw new \Exception("Clase no encontrada");
    }

    // IDs de alumnos en la clase
    $alumnosEnClase = $clase->alumnos()->pluck('alumnos.id')->toArray();

    // Todos los alumnos
    $alumnos = Alumno::with('usuario')->get();

    // Formato tipo checklist
    $resultado = $alumnos->map(function ($alumno) use ($alumnosEnClase) {
        return [
            'codigo_institucional' => $alumno->codigo_institucional,
            'nombre' => $alumno->usuario->nombre ?? '',
            'apellidos' => $alumno->usuario->apellidos ?? '',
            'checked' => in_array($alumno->id, $alumnosEnClase)
        ];
    });

    return $resultado;
}


public function mostrarClasePorId($id)
{
    $clase = Clase::find($id);

    if (!$clase) {
        throw new \Exception("Clase no encontrada");
    }

    return $clase;
}

}