<?php

namespace App\Models;

use App\Observers\PeriodoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mes;


#[ObservedBy(PeriodoObserver::class)]
class Periodo extends Model
{
    /** @use HasFactory<\Database\Factories\PeriodoFactory> */
    use HasFactory;

    protected $table = 'periodos';

    protected $fillable = [
        'ciclo_escolar',
        'cuatrimestre_id',
        'generacion_id',
        'mes_id',
        'inicio_periodo',
        'termino_periodo',
    ];



    public function mes()
    {
        return $this->belongsTo(Mes::class);
    }
    public function cuatrimestre()
    {
        return $this->belongsTo(Cuatrimestre::class, 'cuatrimestre_id');
    }

    public function generacion()
    {
        return $this->belongsTo(Generacion::class);
    }
}
