<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuatrimestre extends Model
{
    protected $table = 'cuatrimestres';
    /** @use HasFactory<\Database\Factories\CuatrimestreFactory> */
    use HasFactory;

    protected $fillable = [
        'cuatrimestre',
        'nombre_cuatrimestre',
        'mes_id'
    ];
    public function mes()
    {
        return $this->belongsTo(Mes::class);
    }
    public function periodos()
    {
        return $this->hasMany(Periodo::class);
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function materias()
    {
        return $this->hasMany(Materia::class);
    }

    public function asignacionMaterias()
    {
        return $this->hasMany(AsignacionMateria::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }



}
