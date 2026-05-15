<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clase extends Model
{
    protected $table = 'clases';

    protected $fillable = [
        'nombre',
        'docente_id'
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class,'docente_id');
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }

    public function alumnos()
{
    return $this->belongsToMany(
        Alumno::class,
        'alumnos_clases',
        'clase_id',
        'alumno_id'
    );
}


}
