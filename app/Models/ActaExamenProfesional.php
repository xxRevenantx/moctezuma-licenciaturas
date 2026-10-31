<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActaExamenProfesional extends Model
{
    protected $table = 'actas_examen_profesional';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_examen' => 'date',
        'acuerdo_fecha' => 'date',
        'fecha_revision' => 'date',
        'autorizado_en' => 'datetime',
        'sinodo' => 'array',
        'datos_alumno' => 'array',
        'incluir_foto' => 'boolean',
        'excepcion_egreso' => 'boolean',
    ];

    public function alumno()
    {
        return $this->belongsTo(Inscripcion::class, 'alumno_id');
    }

    public function historial()
    {
        return $this->hasMany(ActaExamenProfesionalHistorial::class, 'acta_id')->latest('id');
    }

    public function registrar(string $accion, array $detalles = []): void
    {
        $this->historial()->create([
            'usuario_id' => auth()->id(),
            'accion' => $accion,
            'detalles' => $detalles,
        ]);
    }
}
