<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipo_unidad extends Model
{
    protected $table = 'tipo_unidads';

    protected $fillable = [
        'nombre'
    ];



    /**
     * Relacion con la tabla unidad_academica
     */
    public function unidad_academicas()
    {
        return $this->hasMany(Unidad_academica::class);
    }
}
