<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'alumnos';

    protected $fillable = [
        'codigo_institucional',
        'usuario_id',
         'carrera_id'
    ];

     /**
     * Relación con usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

   

       /**
     * Relacion con grupo
     */
    public function clases()
    {
    return $this->belongsToMany(Clase::class);
    }


/**
 * Relacion con la tabla asistencia
 */
public function asistencias(){
    return $this->hasMany(Asistencia::class);
}
}
