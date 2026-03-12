<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
     protected $table = 'grupos';

    protected $fillable = [
        'unidad_academica_id',
        'docente_id',
        'carrera_id',
        'periodo_id',
        'semestre',
        'turno'
    ];

    /**
     * Relacion con la tabla unidad_academica
     */
     public function unidad_academica()
    {
        return $this->belongsTo(Unidad_academica::class, 'unidad_academica_id');
    }

     /**
     * Relacion con tabla la docente
     */
     public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

     /**
     * Relacion con la tabla carrera
     */
     public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

     /**
     * Relacion con la tabla periodo_academico
     */
     public function periodo_academico()
    {
        return $this->belongsTo(Periodo_academico::class, 'periodo_id');
    }


       /**
     * Relacion con alumno
     */
     public function alumno()
    {
    return $this->belongsToMany(Alumno::class);
    }
}
