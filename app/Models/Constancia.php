<?php

namespace App\Models;

use App\Observers\ConstanciaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


#[ObservedBy(ConstanciaObserver::class)]
class Constancia extends Model
{
    protected $table = 'constancias';
    /** @use HasFactory<\Database\Factories\AccionFactory> */
    use HasFactory;

    protected $fillable = [
        'alumno_id',
        'tipo_constancia',
        'no_constancia',
        'fecha_expedicion',

    ];


    public function alumno()
    {
        return $this->belongsTo(Inscripcion::class, 'alumno_id');
    }

}
