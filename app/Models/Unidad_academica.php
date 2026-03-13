<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad_academica extends Model
{
    protected $table = 'unidad_academicas';

    protected $fillable = [
        'nombre',
        'tipo_unidad_id'
    ];

    /**
     * Relacion con la tabla tipo_unidad
     */
     public function tipo_unidad()
    {
        return $this->belongsTo(Tipo_unidad::class);
    }

     /**
     * Relación con la tabla grupo
     */
    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }
}
