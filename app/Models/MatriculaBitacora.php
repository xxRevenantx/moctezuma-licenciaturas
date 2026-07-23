<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatriculaBitacora extends Model
{
    use HasFactory;

    protected $table = 'matricula_bitacoras';

    protected $fillable = [
        'inscripcion_id',
        'user_id',
        'accion',
        'valor_anterior',
        'valor_nuevo',
        'detalles',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'detalles' => 'array',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
