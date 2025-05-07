<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Generacion extends Model
{

    protected $table = 'generaciones';

    /** @use HasFactory<\Database\Factories\GeneracionFactory> */
    use HasFactory;

    protected $fillable = [
        'generacion',
        'activa',
    ];

    public function asignarGeneracion()
    {
        return $this->hasMany(AsignarGeneracion::class);
    }

    public function periodos()
    {
        return $this->hasMany(Periodo::class);
    }

    public function inscripcion()
    {
        return $this->hasMany(Inscripcion::class);
    }



}
