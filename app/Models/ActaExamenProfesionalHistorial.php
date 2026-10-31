<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActaExamenProfesionalHistorial extends Model
{
    protected $table = 'actas_examen_profesional_historial';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['detalles' => 'array', 'created_at' => 'datetime'];
}
