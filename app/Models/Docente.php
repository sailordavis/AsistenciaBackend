<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
     protected $table = 'docentes';

      protected $fillable = [
        'numero_nomina',
        'usuario_id'
    ];

     /**
     * Relación con usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    


   /**
    * Relacion con tabla grupos
    */
   public function grupos()
   {
    return $this->hasMany(Grupo::class);
   }
}
