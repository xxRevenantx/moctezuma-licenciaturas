<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licenciatura extends Model
{
    /** @use HasFactory<\Database\Factories\LicenciaturaFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'RVOE',
        'nombre_corto',
        'imagen',
        'slug',
    ];



    public function asignarGeneraciones()
    {
        return $this->hasMany(AsignarGeneracion::class);
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    // Materias
    public function materias()
    {
        return $this->hasMany(Materia::class);
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

    // Calificaciones
    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }

    // Grupo
    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }

}
