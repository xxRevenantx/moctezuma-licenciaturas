<?php

namespace App\Models;

use App\Observers\MateriaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


#[ObservedBy(MateriaObserver::class)]
class Materia extends Model
{
    /** @use HasFactory<\Database\Factories\MateriaFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'clave',
        'creditos',
        'cuatrimestre_id',
        'licenciatura_id',
        'orden',
        'calificable',
    ];


    public function cuatrimestre()
    {
        return $this->belongsTo(Cuatrimestre::class);
    }

    public function licenciatura()
    {
        return $this->belongsTo(Licenciatura::class);
    }

    // Materias
    public function materias()
    {
        return $this->hasMany(Materia::class);
    }

    public function asignacionMaterias()
    {
        return $this->hasMany(AsignacionMateria::class);
    }



}
