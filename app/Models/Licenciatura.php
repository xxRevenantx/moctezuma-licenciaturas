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
        'fecha_acuerdo',
        'nombre_corto',
        'imagen',
        'slug',
    ];

    protected $casts = [
        'fecha_acuerdo' => 'date',
    ];

    public function getFechaAcuerdoFormateadaAttribute(): ?string
    {
        return $this->fecha_acuerdo?->locale('es')->translatedFormat('d \d\e F \d\e Y');
    }

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
