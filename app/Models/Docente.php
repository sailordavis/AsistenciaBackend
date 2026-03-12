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
     * Relacion con carrera
     */
public function carreras()
{
    return $this->belongsToMany(Carrera::class);
}
}
