<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalificacionAuditoria extends Model
{
    protected $table = 'calificacion_auditorias';

    protected $fillable = [
        'calificacion_id',
        'alumno_id',
        'asignacion_materia_id',
        'profesor_id',
        'licenciatura_id',
        'modalidad_id',
        'generacion_id',
        'cuatrimestre_id',
        'user_id',
        'valor_anterior',
        'valor_nuevo',
        'origen',
        'accion',
        'detalle',
    ];

    protected function casts(): array
    {
        return ['detalle' => 'array'];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Inscripcion::class, 'alumno_id');
    }
}
