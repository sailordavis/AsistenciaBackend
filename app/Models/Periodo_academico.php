<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo_academico extends Model
{
     protected $table = 'periodo_academicos';

      protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'activo'
    ];

     /**
    * Relacion con tabla grupos
    */
   public function grupos()
   {
    return $this->hasMany(Grupo::class);
   }
}
