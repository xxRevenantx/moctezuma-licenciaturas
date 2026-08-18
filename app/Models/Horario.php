<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    /** @use HasFactory<\Database\Factories\HorarioFactory> */
    use HasFactory;

 protected $fillable = [
        'hora', 'dia_id', 'licenciatura_id', 'cuatrimestre_id', 'modalidad_id',
        'generacion_id', 'asignacion_materia_id'
    ];

    public function dia() { return $this->belongsTo(Dia::class); }
    public function licenciatura() { return $this->belongsTo(Licenciatura::class); }
    public function cuatrimestre() { return $this->belongsTo(Cuatrimestre::class); }
    public function modalidad() { return $this->belongsTo(Modalidad::class); }
    public function generacion() { return $this->belongsTo(Generacion::class); }
    // La materia tampoco vive directamente en horarios.
    public function getMateriaAttribute()
    {
        return $this->asignacionMateria?->materia;
    }
    public function asignacionMateria() { return $this->belongsTo(AsignacionMateria::class); }

    // El profesor no vive en horarios; se resuelve mediante asignacionMateria->profesor.
    public function getProfesorAttribute()
    {
        return $this->asignacionMateria?->profesor;
    }



}
