<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mes extends Model
{
    protected $table = 'meses';
    /** @use HasFactory<\Database\Factories\MesFactory> */
    use HasFactory;

    protected $fillable = [
        'meses',
        'meses_corto'
    ];

    public function periodos()
    {
        return $this->hasMany(Periodo::class);
    }

    public function cuatrimestre()
    {
        return $this->hasMany(Cuatrimestre::class);
    }
}
