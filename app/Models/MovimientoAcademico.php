<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoAcademico extends Model
{
    use HasFactory;

    protected $table = 'movimientos_academicos';

    protected $fillable = [
        'lote',
        'tipo',
        'inscripcion_id',
        'matricula_snapshot',
        'matricula_interna_snapshot',
        'alumno_snapshot',
        'licenciatura_id',
        'generacion_id',
        'modalidad_origen_id',
        'modalidad_destino_id',
        'cuatrimestre_origen_id',
        'cuatrimestre_destino_id',
        'motivo',
        'observaciones',
        'calificaciones_trasladadas',
        'estado',
        'ejecutado_por',
        'revertido_por',
        'revertido_at',
        'detalles',
    ];

    protected function casts(): array
    {
        return [
            'detalles' => 'array',
            'revertido_at' => 'datetime',
            'calificaciones_trasladadas' => 'integer',
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

    public function modalidadOrigen()
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_origen_id');
    }

    public function modalidadDestino()
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_destino_id');
    }

    public function cuatrimestreOrigen()
    {
        return $this->belongsTo(Cuatrimestre::class, 'cuatrimestre_origen_id');
    }

    public function cuatrimestreDestino()
    {
        return $this->belongsTo(Cuatrimestre::class, 'cuatrimestre_destino_id');
    }

    public function ejecutor()
    {
        return $this->belongsTo(User::class, 'ejecutado_por');
    }

    public function reversionUsuario()
    {
        return $this->belongsTo(User::class, 'revertido_por');
    }
}
