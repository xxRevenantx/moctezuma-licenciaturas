<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licenciatura extends Model
{
    /** @use HasFactory<\Database\Factories\LicenciaturaFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'RVOE',
        'nombre_corto',
        'imagen',
        'slug',
    ];



}
