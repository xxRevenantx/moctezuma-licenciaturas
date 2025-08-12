<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos';

    protected $fillable = [
        'licenciatura_id',
        'cuatrimestre_id',
        'grupo',
    ];

    public function licenciatura()
    {
        return $this->belongsTo(Licenciatura::class);
    }

    public function cuatrimestre()
    {
        return $this->belongsTo(Cuatrimestre::class);
    }

    /**
     * Scope para ordenar por nombre de licenciatura ASC sin perder columnas de grupos.
     */
    public function scopeOrderByLicenciatura($query)
    {
        return $query
            ->join('licenciaturas', 'grupos.licenciatura_id', '=', 'licenciaturas.id')
            ->orderBy('licenciaturas.id', 'asc')
            ->select('grupos.*');
    }
}
