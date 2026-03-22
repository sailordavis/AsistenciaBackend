<?php

namespace App\Modules\Group\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Grupo;
use Exception;

class GroupService
{
    

public function procesarCSV($file)
{
    $usuario = Auth::user();

    if (!$usuario || !$usuario->docente) {
        throw new \Exception("Usuario no autorizado");
    }

    $docenteId = $usuario->docente->id;

    //  Actualizar periodos vencidos
    DB::table('periodo_academicos')
        ->where('fecha_fin', '<', now())
        ->update(['activo' => false]);

    $handle = fopen($file->getRealPath(), 'r');

    if ($handle === false) {
        throw new \Exception("No se pudo abrir el archivo");
    }

    fgetcsv($handle); // saltar encabezado

    $errores = [];
    $procesados = 0;
    $creados = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {

        DB::beginTransaction();
    
        try {
            $procesados++;
            //  Validación básica de columnas
            if (count($data) < 8) {
                throw new \Exception("Columnas incompletas");
            }

            [
                $unidadNombre,
                $tipoUnidadNombre,
                $carreraNombre,
                $periodoNombre,
                $fechaInicio,
                $fechaFin,
                $semestre,
                $turno
            ] = $data;

            $tipoUnidadNombre = $this->normalizarTexto($tipoUnidadNombre);

            //  Validar fechas
            if (!strtotime($fechaInicio) || !strtotime($fechaFin)) {
                throw new \Exception("Fechas invalidas");
            }
            //Validar semestre
            if(is_numeric($semestre)){
                if($semestre < 1 || $semestre > 9){
                   throw new \Exception("Semestre invalido");    
                }

                if($tipoUnidadNombre !== 'materia'){
                   throw new \Exception("El grupo no requiere semestre");
                }
            }else if(empty($semestre)){
                if($tipoUnidadNombre === 'materia'){
                    throw new \Exception("Los grupos de clase requieren un semestre valido");
                }
            }else{
                throw new \Exception("Semestre invalido"); 
            }

           
            
            //Seccion de codigo donde se obtendran los id's de otras tablas
            //para la creacion de los campos de la tabla grupos
            //en caso contrario se crearan esos campos a excepcion de carrera

            //  Unidad académica
            $unidadId = $this->obtenerOCrearSegunTabla('unidad_academicas', [
                'nombre' => $unidadNombre,
                'tipoUnidadNombre' => $tipoUnidadNombre
            ]);
            //Carrera

                if ($tipoUnidadNombre === 'materia' && empty($carreraNombre)) {
                throw new \Exception("Los grupos de clase requieren una relacion con carrera");
                }

               $carreraId = empty($carreraNombre)
               ? null
               : $this->obtenerOCrearSegunTabla('carreras', [
               'nombre' => $carreraNombre,]);
             
             

            //  Periodo
            $periodoId = $this->obtenerOCrearSegunTabla('periodo_academicos', [
                'nombre' => $periodoNombre,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]);

            //Evitar duplicados
            $query = DB::table('grupos')
            ->where('unidad_academica_id', $unidadId)
            ->where('docente_id', $docenteId)
            ->where('periodo_id', $periodoId)
             ->where('turno', $turno);

            if ($semestre !== null) {
            $query->where('semestre', $semestre);
            } else {
            $query->whereNull('semestre');
             }

            if ($carreraId) {
            $query->where('carrera_id', $carreraId);
            } else {
            $query->whereNull('carrera_id');
            }

           

           if ($query->exists()) {
            throw new \Exception("Grupo ya creado anteriormente");
            }

         Grupo::create([
         'unidad_academica_id' => $unidadId,
         'docente_id' => $docenteId,
         'periodo_id' => $periodoId,
         'semestre' => $semestre !== null ? trim($semestre) : null,
         'turno' => trim($turno),
         'carrera_id' => $carreraId ?? null,
        ]);

            
            
            DB::commit();
            $creados++;
            
        } catch (\Exception $e) {

            DB::rollBack();

            $errores[] = [
                'fila' => $data,
                'error' => $e->getMessage()
            ];

            continue;
        }   
    }

    fclose($handle);

    return [
        'message' => 'Proceso terminado',
        'filas_procesadas' => $procesados,
        'grupos_creados' => $creados,
        'errores' => $errores
    ];
}




   private function obtenerOCrearSegunTabla($tabla, $data)
{
    if (empty($data['nombre'])) {
        throw new \Exception("Nombre vacío en {$tabla}");
       }
   
       $nombreNormalizado = $this->normalizarTexto($data['nombre']);
    
    
    switch ($tabla) {

        //  TABLAS SIMPLES
        case 'carreras':
        case 'tipo_unidads':
         $registros = DB::table($tabla)->select('id', 'nombre')->get();
            

            foreach ($registros as $registro) {
                if ($this->normalizarTexto($registro->nombre) === $nombreNormalizado) {
                    return $registro->id;
                }
            }

            throw new \Exception("El valor no existe en {$tabla}: " . $data['nombre']);

        //  UNIDAD ACADÉMICA
        case 'unidad_academicas':

            $tipoUnidadId = $this->obtenerOCrearSegunTabla('tipo_unidads', [
                'nombre' => $data['tipoUnidadNombre']
            ]);

           
            $registros = DB::table($tabla)
                ->where('tipo_unidad_id', $tipoUnidadId)
                ->select('id', 'nombre')
                ->get();

         foreach ($registros as $registro) {
                if ($this->normalizarTexto($registro->nombre) === $nombreNormalizado) {
                    return $registro->id;
                }
            }

            return DB::table($tabla)->insertGetId([
                'nombre' => trim($data['nombre']),
                'tipo_unidad_id' => $tipoUnidadId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

        //  PERIODO ACADÉMICO
        case 'periodo_academicos':

             if (!isset($data['fecha_inicio']) || !isset($data['fecha_fin'])) {
                throw new \Exception("Periodo requiere fechas");
            }

            $registros = DB::table($tabla)->select('id', 'nombre')->get();

            foreach ($registros as $registro) {
                if ($this->normalizarTexto($registro->nombre) === $nombreNormalizado) {
                    return $registro->id;
                }
            }

            $activo = now()->between($data['fecha_inicio'], $data['fecha_fin']);

            return DB::table($tabla)->insertGetId([
                'nombre' => trim($data['nombre']), //  original
                'fecha_inicio' => trim($data['fecha_inicio']),
                'fecha_fin' => trim($data['fecha_fin']),
                'activo' => $activo,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            

        default:
            throw new \Exception("Tabla no soportada: " . $tabla);
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