<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{

    use HasApiTokens; 

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellidos',
        'correoinstitucional',
        'password',
        'rol_id'
    ];

    protected $hidden = [
        'password'
    ];

    /**
     * Mutator para hashear automáticamente la contraseña
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Relación con la tabla rols
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }
    /**
     * Relacion con la tabla administrador 
     */
    public function administrador()
{
    return $this->hasOne(Administrador::class);
}
/**
 * Relacion con la tabla docente 
 */
public function docente()
{
    return $this->hasOne(Docente::class);
}
/**
 * Relacion con la tabla alumno 
 */
public function alumno(){
    return $this->hasOne(Alumno::class);
}

/**
     * Relación con la tabla eventos
     */
    public function eventos()
    {
        return $this->hasMany(Evento::class, 'creador_id');
    }

    


     


}