<?php

namespace App\Models;

use App\Observers\AsignarGeneracionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

#[ObservedBy(AsignarGeneracionObserver::class)]
class AsignarGeneracion extends Model
{
    /** @use HasFactory<\Database\Factories\AsignarGeneracionFactory> */
    use HasFactory;

    protected $table = 'asignar_generaciones';

    protected $fillable = [

        'licenciatura_id',
        'modalidad_id',
        'generacion_id',
    ];

    public function licenciatura()
    {
        return $this->belongsTo(Licenciatura::class);
    }
    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class);
    }
    public function generacion()
    {
        return $this->belongsTo(Generacion::class);
    }
}
