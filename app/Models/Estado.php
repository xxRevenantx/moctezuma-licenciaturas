<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;


    protected $table = 'estados';



    protected $fillable = [
        'nombre',
    ];




    public function inscripciones_como_nacimiento()
    {
        return $this->hasMany(Inscripcion::class, 'estado_nacimiento_id');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'estado_id');
    }




}
