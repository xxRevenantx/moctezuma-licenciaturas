<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizacionDocumentoIdentidad extends Model
{
    use HasFactory;

    protected $table = 'organizaciones_documentos_identidad';

    protected $fillable = [
        'inscripcion_id',
        'version',
        'estado',
        'asignaciones',
        'fuentes_ids',
        'confirmado_por',
        'confirmado_at',
        'metadatos',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'asignaciones' => 'array',
            'fuentes_ids' => 'array',
            'confirmado_at' => 'datetime',
            'metadatos' => 'array',
        ];
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function usuarioConfirmacion()
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('estado', 'confirmado');
    }

    public function scopeBorradores($query)
    {
        return $query->where('estado', 'borrador');
    }
}
