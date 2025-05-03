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
}
