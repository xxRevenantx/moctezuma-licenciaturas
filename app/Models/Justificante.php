<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justificante extends Model
{
    /** @use HasFactory<\Database\Factories\JustificanteFactory> */
    use HasFactory;

    protected $fillable = [
        'alumno_id',
        'fechas_justificacion',
        'justificacion',
        'fecha_expedicion',
    ];

    public function alumno()
    {
        return $this->belongsTo(Inscripcion::class);
    }

}
