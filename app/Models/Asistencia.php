<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'asistencias';

      protected $fillable = [
        'evento_id',
        'alumno_id',
        'estado',
        'hora_registro'
    ];

    public function alumno(){
        return $this->belongsTo(Alumno::class);
    }

    public function evento(){
        return $this->belongsTo(Evento::class);
    }
}
