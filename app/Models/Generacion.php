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
        'egresada',
    ];

}
