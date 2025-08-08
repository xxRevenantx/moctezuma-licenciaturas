<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{

    use HasFactory;

    protected $table = 'ciudades';

    protected $fillable = [
        'nombre',
    ];



    public function inscripciones_como_nacimiento()
    {
        return $this->hasMany(Inscripcion::class, 'ciudad_nacimiento_id');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'ciudad_id');
    }




}
