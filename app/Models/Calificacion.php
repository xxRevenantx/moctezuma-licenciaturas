<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    /** @use HasFactory<\Database\Factories\CalificacionFactory> */
    use HasFactory;

    protected $table = 'calificaciones';

    protected $fillable = [
        'alumno_id',
        'asignacion_materia_id',
        'modalidad_id',
        'generacion_id',
        'licenciatura_id',
        'cuatrimestre_id',
        'profesor_id',
        'calificacion'

    ];

    public function alumno()
    {
        return $this->belongsTo(Inscripcion::class, 'alumno_id');
    }

    public function asignacionMateria()
    {
        return $this->belongsTo(AsignacionMateria::class);
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class);
    }

    public function generacion()
    {
        return $this->belongsTo(Generacion::class);
    }

    public function licenciatura()
    {
        return $this->belongsTo(Licenciatura::class);
    }

    public function cuatrimestre()
    {
        return $this->belongsTo(Cuatrimestre::class);
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }


}
