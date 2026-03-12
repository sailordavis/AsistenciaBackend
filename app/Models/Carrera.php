<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $table = 'carreras';

      protected $fillable = [
        'nombre'
    ];


    /**
     * Relacion con docentes
     */
    public function docentes()
{
    return $this->belongsToMany(Docente::class);
}


  /**
   * Relacion con alumnos 
   */
  public function alumnos()
  {
    return $this->hasMany(Alumno::class, 'carrera_id');
  }
}
