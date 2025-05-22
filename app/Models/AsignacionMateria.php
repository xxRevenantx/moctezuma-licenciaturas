<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class AsignacionMateria extends Model
{
    use HasFactory;
    protected $fillable = [
        'materia_id',
        'licenciatura_id',
        'modalidad_id',
        'cuatrimestre_id',
        'profesor_id',
    ];
    protected $table = 'asignacion_materias';


    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function licenciatura()
    {
        return $this->belongsTo(Licenciatura::class);
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class);
    }

    public function cuatrimestre()
    {
        return $this->belongsTo(Cuatrimestre::class);
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }


}
