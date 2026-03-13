<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'asistencias';

    protected $fillable = [
        'evento_id',
        'alumno_id',
        'hora_entrada',
        'hora_salida'
    ];

   

    // Una asistencia pertenece a un evento
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    // Una asistencia pertenece a un alumno (usuario)
    public function alumno()
    {
        return $this->belongsTo(Usuario::class, 'alumno_id');
    }
}
