<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoIdentidadFuente extends Model
{
    use HasFactory;

    protected $table = 'documentos_identidad_fuentes';

    protected $fillable = [
        'inscripcion_id',
        'documento_identidad_id',
        'ruta',
        'ruta_original',
        'nombre_original',
        'nombre_almacenado',
        'mime_type',
        'mime_original',
        'tamano',
        'hash_sha256',
        'paginas',
        'estado',
        'subido_por',
        'metadatos',
    ];

    protected function casts(): array
    {
        return [
            'tamano' => 'integer',
            'paginas' => 'integer',
            'metadatos' => 'array',
        ];
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function documentoIdentidad()
    {
        return $this->belongsTo(DocumentoIdentidad::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activo');
    }
}
