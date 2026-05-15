<?php

namespace App\Modules\Class\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\Clase;
use App\Models\Alumno;
use Exception;

class ClassService
{
    //Funciones para el rol docente
    //Mostrar  todas las clases del docente
    public function mostrarClases($docenteId)
    {
    // Retornar clases propias del docente
    return Clase::where('docente_id', $docenteId)
        ->orderBy('created_at', 'desc')
        ->get();
    }

    //  Mostrar una clase
    public function mostrarUnaClase($nombre,$docenteId)
    {
         $clase = Clase::where('nombre', $nombre)
        ->where('docente_id', $docenteId)
        ->first();

        if (!$clase) {
            throw new \Exception("Clase no encontrada");
        }

        return $clase;
    }

    //  Eliminar una clase
    public function eliminarClase($id,$docenteId)
    {
         $clase = Clase::where([
         'id' => $id,
        'docente_id' => $docenteId
          ])->first();

        if (!$clase) {
            throw new \Exception("Clase no encontrada");
        }

        $clase->delete();

        return true;
    }

    //Actualizar una clase 
    public function actualizarClase($id, $docenteId, $data)
{
    $clase = Clase::where('id', $id)
        ->where('docente_id', $docenteId)
        ->first();

    if (!$clase) {
        throw new \Exception("Clase no encontrada");
    }

    $clase->update($data);

    return $clase;
}



   //Mostrar listas de alumnos por clase

   public function mostrarAlumnosClase($claseId, $docenteId)
{
    $clase = Clase::with('alumnos.usuario')
        ->where('id', $claseId)
        ->where('docente_id', $docenteId)
        ->first();

    if (!$clase) {
        throw new \Exception("Clase no encontrada");
    }

    return $clase->alumnos;
}

//Funcion para el rol alumno 

public function obtenerMisClases($usuarioId)
{
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

    // Obtener clases inscritas
    $clases = $alumno->clases()
        ->with([
            'docente.usuario'
        ])
        ->orderBy('nombre', 'asc')
        ->get();

    return $clases;
}




}