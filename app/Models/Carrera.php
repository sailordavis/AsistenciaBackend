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
   * Relacion con alumnos 
   */
  public function alumnos()
  {
    return $this->hasMany(Alumno::class);
  }

  /**
   * Relacion con la tabla grupo
   */
  public function grupos()
  {
    return $this->hasMany(Grupo::class,);
  }
}
