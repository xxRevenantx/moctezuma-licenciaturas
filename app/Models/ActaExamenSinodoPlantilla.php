<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActaExamenSinodoPlantilla extends Model
{
    protected $table = 'actas_examen_sinodos_plantillas';
    protected $guarded = ['id'];
    protected $casts = ['integrantes' => 'array'];
}
