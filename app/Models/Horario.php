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
        'generacion_id', 'asignacion_materia_id', 'profesor_id'
    ];

    public function dia() { return $this->belongsTo(Dia::class); }
    public function licenciatura() { return $this->belongsTo(Licenciatura::class); }
    public function cuatrimestre() { return $this->belongsTo(Cuatrimestre::class); }
    public function modalidad() { return $this->belongsTo(Modalidad::class); }
    public function generacion() { return $this->belongsTo(Generacion::class); }
    public function asignacionMateria() { return $this->belongsTo(AsignacionMateria::class); }
    public function profesor() { return $this->belongsTo(Profesor::class); }



}
