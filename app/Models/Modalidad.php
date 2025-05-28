<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modalidad extends Model
{
    /** @use HasFactory<\Database\Factories\ModalidadeFactory> */
    use HasFactory;

    protected $table = 'modalidades';

    protected $fillable = [
        'nombre',
        'slug',
    ];

    public function asignarGeneracion()
    {
        return $this->hasMany(AsignarGeneracion::class);
    }

    public function inscripcion()
    {
        return $this->hasMany(Inscripcion::class);
    }


    // Asignacion Materias
    public function asignacionMaterias()
    {
        return $this->hasMany(AsignacionMateria::class);
    }



    // Horarios
    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    //Modalidad
    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }

}
