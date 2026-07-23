<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DescargaExpedienteIdentidad extends Model
{
    use HasFactory;

    protected $table = 'descargas_expedientes_identidad';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'formato',
        'estado',
        'filtros',
        'total_alumnos',
        'alumnos_procesados',
        'alumnos_incompletos',
        'documentos_faltantes',
        'archivo_ruta',
        'archivo_nombre',
        'archivo_tamano',
        'error',
        'ip',
        'user_agent',
        'solicitado_at',
        'iniciado_at',
        'completado_at',
        'descargado_at',
    ];

    protected function casts(): array
    {
        return [
            'filtros' => 'array',
            'total_alumnos' => 'integer',
            'alumnos_procesados' => 'integer',
            'alumnos_incompletos' => 'integer',
            'documentos_faltantes' => 'integer',
            'archivo_tamano' => 'integer',
            'solicitado_at' => 'datetime',
            'iniciado_at' => 'datetime',
            'completado_at' => 'datetime',
            'descargado_at' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function estaLista(): bool
    {
        return $this->estado === 'listo' && filled($this->archivo_ruta);
    }
}
