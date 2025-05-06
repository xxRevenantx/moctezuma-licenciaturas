<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    protected $table = 'inscripciones';
    /** @use HasFactory<\Database\Factories\InscripcionFactory> */
    use HasFactory;
}
