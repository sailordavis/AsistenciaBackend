<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $table = 'eventos';

    protected $fillable = [
        'titulo',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'tipo',
        'clase_id',
        'creador_id',
        'token_evento',
        'latitud',
        'longitud',
         'radio_permitido',
         'direccion_texto',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'creador_id');
    }

    public function clase()
    {
        return $this->belongsTo(Clase::class,'clase_id');
    }

    public function asistencias(){
    return $this->hasMany(Asistencia::class);
    }

    public function registradores()
{
    return $this->belongsToMany(
        Usuario::class,
        'evento_registradores',
        'evento_id',
        'usuario_id'
    );
}
}
