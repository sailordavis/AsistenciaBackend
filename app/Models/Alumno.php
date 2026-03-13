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
     * Relación con carrera
     */
    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

       /**
     * Relacion con grupo
     */
    public function grupos()
    {
    return $this->belongsToMany(Grupo::class);
    }


/**
 * Relacion con la tabla asistencia
 */
public function asistencias(){
    return $this->hasMany(Asistencia::class);
}
}
