<?php

namespace App\Modules\Management\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Alumno;

class ManagementService{


   
    public function procesarCSVDocentes($file)
{
    $handle = fopen($file->getRealPath(), 'r');

    fgetcsv($handle); // encabezado

    $errores = [];
    $creados = 0;
    $credenciales = [];

    while (($data = fgetcsv($handle, 1000, ",")) !== false) {

        DB::beginTransaction();

        try {

            if (count($data) < 4) {
                throw new \Exception("Columnas incompletas");
            }

            [
                $nombre,
                $apellidos,
                $correo,
                $numero_nomina
            ] = $data;

            //  Normalizacion para ingresar correo a la base de datos
            $correo= $this->normalizarCorreo($correo);

            //  Validar formato de correo docente
            if (!$this->validarCorreoDocente($correo)) {
                throw new \Exception("Formato de correo invalido");
            }
            //Evitar duplicados
            if (Usuario::whereRaw('LOWER(correoInstitucional) = ?', [$correo])->exists()) {
                throw new \Exception("Correo ya registrado");
            }

             $passwordPlano = Str::random(10);

            //  Crear usuario 
            $usuario = Usuario::create([
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'correoInstitucional' => $correo, 
                'password' => $passwordPlano,
                'rol_id' => 2 //rol docente
            ]);

            $credenciales[] = [
           'correo' => $usuario->correoInstitucional,
            'password' => $passwordPlano
             ];

            //  Validar duplicado de nómina
            if (Docente::where('numero_nomina', $numero_nomina)->exists()) {
                throw new \Exception("Número de nómina ya registrado");
            }

            //  Crear docente
            Docente::create([
                'usuario_id' => $usuario->id,
                'numero_nomina' => trim($numero_nomina)
            ]);

            DB::commit();
            $creados++;

        } catch (\Exception $e) {

            DB::rollBack();

            $errores[] = [
                'fila' => $data,
                'error' => $e->getMessage()
            ];
        }
    }

    fclose($handle);

    return [
        'tipo' => 'docentes',
        'creados' => $creados,
        'credenciales' => $credenciales,
        'errores' => $errores
    ];
}

            


     public function procesarCSVAlumnos($file)
{
    $handle = fopen($file->getRealPath(), 'r');

    fgetcsv($handle); // encabezado

    $errores = [];
    $creados = 0;
    $credenciales = [];

    while (($data = fgetcsv($handle, 1000, ",")) !== false) {

        DB::beginTransaction();

        try {

            if (count($data) < 5) {
                throw new \Exception("Columnas incompletas");
            }

            [
                $nombre,
                $apellidos,
                $correo,
                $codigo_institucional,
                $carrera
            ] = $data;

            //  Normalizar correo 
            $correo = $this->normalizarCorreo($correo);

            //  Validar formato alumno
            if (!$this->validarCorreoAlumno($correo)) {
                throw new \Exception("Formato de correo inválido");
            }

            //  Evitar duplicados 
           if (Usuario::whereRaw('LOWER(correoInstitucional) = ?', [$correo])->exists()) {
           throw new \Exception("Correo ya registrado");
}

            //  Buscar carrera 
            $carreraInput = $this->normalizarTexto($carrera);
            $carreras = DB::table('carreras')->get();

            $carreraId = null;

            foreach ($carreras as $carrera) {
                if ($this->normalizarTexto($carrera->nombre) === $carreraInput) {
                    $carreraId = $carrera->id;
                    break;
                }
            }

            if (!$carreraId) {
                throw new \Exception("Carrera no encontrada: " . $carrera);
            }
            $passwordPlano = Str::random(10);

            //  Crear usuario
            $usuario = Usuario::create([
                'nombre' => $nombre,
                'apellidos' =>$apellidos,
                'correoInstitucional' => $correo, 
                'password' => $passwordPlano,
                'rol_id' => 3 // alumno
            ]);

            $credenciales[] = [
           'correo' => $usuario->correoInstitucional,
            'password' => $passwordPlano
             ];

            //  Validar duplicado de código institucional
            if (Alumno::where('codigo_institucional', $codigo_institucional)->exists()) {
                throw new \Exception("Código institucional ya registrado");
            }

            //  Crear alumno
            Alumno::create([
                'usuario_id' => $usuario->id,
                'codigo_institucional' => $codigo_institucional,
                'carrera_id' => $carreraId
            ]);

            DB::commit();
            $creados++;

        } catch (\Exception $e) {

            DB::rollBack();

            $errores[] = [
                'fila' => $data,
                'error' => $e->getMessage()
            ];
        }
    }

    fclose($handle);

    return [
        'tipo' => 'alumnos',
        'creados' => $creados,
        'credenciales' => $credenciales,
        'errores' => $errores
    ];
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

private function validarCorreoAlumno($correo)
{
    return preg_match('/^(za|zap)\d+@zapopan\.tecmm\.edu\.mx$/', $correo);
}

private function validarCorreoDocente($correo)
{
    return preg_match('/^[a-z]+\.[a-z]+@zapopan\.tecmm\.edu\.mx$/', $correo);
}

private function normalizarCorreo($correo)
{
    return strtolower(trim($correo));
}


}
