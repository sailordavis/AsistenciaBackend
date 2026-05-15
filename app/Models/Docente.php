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
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    


   /**
    * Relacion con tabla grupos
    */
   public function clases()
   {
    return $this->hasMany(Clase::class,'docente_id');
   }
}
