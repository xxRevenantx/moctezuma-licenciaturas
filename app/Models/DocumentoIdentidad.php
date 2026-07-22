<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoIdentidad extends Model
{
    use HasFactory;

    protected $table = 'documentos_identidad';

    protected $fillable = [
        'inscripcion_id',
        'tipo',
        'ruta',
        'nombre_original',
        'nombre_almacenado',
        'mime_type',
        'tamano',
        'hash_sha256',
        'version',
        'es_actual',
        'estado',
        'subido_por',
        'fecha_eliminacion',
        'metadatos',
    ];

    protected function casts(): array
    {
        return [
            'es_actual' => 'boolean',
            'tamano' => 'integer',
            'version' => 'integer',
            'fecha_eliminacion' => 'datetime',
            'metadatos' => 'array',
        ];
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function scopeActual($query)
    {
        return $query->where('es_actual', true)->where('estado', 'activo');
    }
}
