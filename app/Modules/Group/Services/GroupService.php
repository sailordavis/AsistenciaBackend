<?php

namespace App\Modules\Group\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Grupo;

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

            //  Validar fechas
            if (!strtotime($fechaInicio) || !strtotime($fechaFin)) {
                throw new \Exception("Fechas inválidas");
            }

            //  Unidad académica
            $unidadId = $this->obtenerOCrearSegunTabla('unidad_academicas', [
                'nombre' => $unidadNombre,
                'tipoUnidadNombre' => $tipoUnidadNombre
            ]);

            //  Carrera
            $carreraId = $this->obtenerOCrearSegunTabla('carreras', [
                'nombre' => $carreraNombre
            ]);

            //  Periodo
            $periodoId = $this->obtenerOCrearSegunTabla('periodo_academicos', [
                'nombre' => $periodoNombre,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]);

            //  Crear grupo
            $grupo = Grupo::firstOrCreate([
                'unidad_academica_id' => $unidadId,
                'docente_id' => $docenteId,
                'periodo_id' => $periodoId,
                'semestre' => $semestre,
                'turno' => $turno,
                'carrera_id' => $carreraId
            ]);

            if ($grupo->wasRecentlyCreated) {
                $creados++;
            }

            $procesados++;

            DB::commit();

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
    switch ($tabla) {

        case 'unidad_academicas':
            $registro = DB::table($tabla)
                ->where('nombre', $data['nombre'])
                ->first();

            if ($registro) return $registro->id;

            return DB::table($tabla)->insertGetId([
               'nombre' => $data['nombre'],
              'tipo_unidad_id' =>  $this->obtenerOCrearSegunTabla('tipo_unidads', [
                'nombre' => $data['tipoUnidadNombre']
              ])
              ]);
           
        case 'carreras':
        case 'tipo_unidads':
            $registro = DB::table($tabla)
                ->where('nombre', $data['nombre'])
                ->first();

            if ($registro) return $registro->id;

            return DB::table($tabla)->insertGetId([
                'nombre' => $data['nombre']
            ]);

        case 'periodo_academicos':
            $registro = DB::table($tabla)
                ->where('nombre', $data['nombre'])
                ->first();

            if ($registro) return $registro->id;

            // Validacion de campos exclusivos de tabla periodo_academicos
            if (!isset($data['fecha_inicio']) || !isset($data['fecha_fin'])) {
                throw new \Exception("Periodo requiere fechas");
            }

           $activo = now()->between($data['fecha_inicio'], $data['fecha_fin']);

    return DB::table($tabla)->insertGetId([
        'nombre' => $data['nombre'],
        'fecha_inicio' => $data['fecha_inicio'],
        'fecha_fin' => $data['fecha_fin'],
        'activo' => $activo
    ]);
        default:
            throw new \Exception("Tabla no soportada");
    }
}
}