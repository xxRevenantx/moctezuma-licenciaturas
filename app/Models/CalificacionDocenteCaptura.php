<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalificacionDocenteCaptura extends Model
{
    protected $table = 'calificacion_docente_capturas';

    protected $fillable = [
        'asignacion_materia_id',
        'profesor_id',
        'licenciatura_id',
        'modalidad_id',
        'generacion_id',
        'cuatrimestre_id',
        'estado',
        'fecha_limite',
        'entregado_at',
        'validado_at',
        'reabierto_at',
        'entregado_por',
        'validado_por',
        'reabierto_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'date',
            'entregado_at' => 'datetime',
            'validado_at' => 'datetime',
            'reabierto_at' => 'datetime',
        ];
    }

    public function asignacionMateria()
    {
        return $this->belongsTo(AsignacionMateria::class);
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }
}
