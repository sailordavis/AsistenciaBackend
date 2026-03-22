<?php
namespace App\Modules\Group\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class FillGroupService{

public function LlenarGrupos($file){

     $usuario = Auth::user();

    if (!$usuario || !$usuario->docente) {
        throw new \Exception("Usuario no autorizado");
    }

    $docenteId = $usuario->docente->id;

  $handle = fopen($file->getRealPath(), 'r');

  fgetcsv($handle); // encabezado

   $errores = [];
   $procesados=0;
    $creados = 0;
    $credenciales = [];

    while (($data = fgetcsv($handle, 1000, ",")) !== false){

       DB::beginTransaction(); 
      try{

      $procesados++; 

         if (count($data) < 6) {
                throw new \Exception("Columnas incompletas");
            }
         [
          $numero_control,
          $asignatura,
          $carrera,
          $turno,
          $periodo_academico,
          $semestre
         ] = $data;

           //  Buscar alumno
          $alumno = DB::table('alumnos')
        ->where('codigo_institucional', trim($numero_control))
        ->first();

    if (!$alumno) {
        throw new \Exception("Alumno con numero de control: {$numero_control} no encontrado");
    }

    

    //  Buscar grupo
    $grupo = $this->ExisteGrupo([
        'asignatura' => $asignatura,
        'carrera' => $carrera,
        'periodo_academico' => $periodo_academico,
        'turno' => $turno,
        'semestre' =>$semestre,
        'docente_id' => $docenteId
    ]);

     if (!$grupo) {
        throw new \Exception("Grupo no encontrado con estas caracteristicas: 
        asignatura: {$asignatura}
        carrera: {$carrera}
        periodo academico: {$periodo_academico}
        turno: {$turno}
        semestre: {$semestre}
        ");
    }

    if($grupo->carrera_id){
       if($grupo->carrera_id!==$alumno->carrera_id){
          throw new \Exception("El alumno no puede pertenecer a una clase fuera de su carrera");  
       }
    }

     //  Evitar duplicados
            $existe = DB::table('grupo__alumnos')
                ->where('grupo_id', $grupo->id)
                ->where('alumno_id', $alumno->id)
                ->exists();

            if ($existe) {
                DB::rollBack();
                continue;
            }

           //  Insertar relación
            DB::table('grupo__alumnos')->insert([
                'grupo_id' => $grupo->id,
                'alumno_id' => $alumno->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        
         DB::commit();
         $creados++;
      }catch(\Exception $e){
         DB::rollBack();
         $errores[] = "Error en fila: " . $e->getMessage();
         continue;
      }
    }
    fclose($handle);

    return [
        'message' => 'Proceso terminado',
        'filas_procesadas' => $procesados,
        'grupos llenados:' => $creados,
        'errores' => $errores
    ];
}

private function ExisteGrupo($data)
{
    // Obtener IDs
    $unidadAcademicaId = $this->ObtenerId('unidad_academicas', $data['asignatura']);
    $periodoId = $this->ObtenerId('periodo_academicos', $data['periodo_academico']);
    $carreraId = $this->ObtenerId('carreras', $data['carrera']);

    // Validaciones
    if (!$unidadAcademicaId) {
        throw new Exception("Unidad académica no encontrada: " . $data['asignatura']);
    }

    if (!$periodoId) {
        throw new Exception("Periodo académico no encontrado: " . $data['periodo_academico']);
    }

    

    // Buscar grupo
    $query = DB::table('grupos')
        ->where('periodo_id', $periodoId)
        ->where('unidad_academica_id', $unidadAcademicaId)
        ->where('turno',$data['turno'])
        ->where('docente_id',$data['docente_id']);
        if ($carreraId) {
            $query->where('carrera_id', $carreraId);
            } else {
            $query->whereNull('carrera_id');
            }

        if ($data['semestre'] !== null) {
            $query->where('semestre', $data['semestre']);
            } else {
            $query->whereNull('semestre');
            }

    $grupo = $query->first();
    return $grupo;
}


private function ObtenerId($table, $nombre)
{
    $nombre = $this->normalizarTexto($nombre);

             $registros = DB::table($table)->select('id', 'nombre')->get();

            foreach ($registros as $registro) {
                if ($this->normalizarTexto($registro->nombre) === $nombre) {
                    return $registro->id;
                }
            }
}

private function normalizarTexto($texto)
    {
        $texto = trim($texto);
        $texto = strtolower($texto);
        // Quitar acentos
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return $texto;
    }

}
