<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialInscripcion extends Model
{
    use HasFactory;

    protected $table = 'historial_inscripciones';

    protected $fillable = [
        'inscripcion_id',
        'user_id',
        'matricula',
        'sexo',
        'licenciatura_id',
        'generacion_id',
        'cuatrimestre_id',
        'modalidad_id',
        'ciclo_escolar',
        'status',
        'egresado',
        'fecha_baja',
        'tipo_movimiento',
        'fecha_movimiento',
    ];

    protected function casts(): array
    {
        return [
            'fecha_baja' => 'datetime',
            'fecha_movimiento' => 'datetime',
        ];
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function licenciatura()
    {
        return $this->belongsTo(Licenciatura::class);
    }

    public function generacion()
    {
        return $this->belongsTo(Generacion::class);
    }

    public function cuatrimestre()
    {
        return $this->belongsTo(Cuatrimestre::class);
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class);
    }
}
