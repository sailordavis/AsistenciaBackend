<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $table = 'eventos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'ubicacion',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'cupo',
        'imagen',
        'creador_id'
    ];

    /**
     * Relación con la tabla usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'creador_id');
    }

      /**
     * Relacion con la tabla grupos
     */
public function grupos()
{
    return $this->belongsToMany(Grupo::class);
}
}
