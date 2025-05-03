<?php

namespace App\Models;

use App\Observers\AccionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


#[ObservedBy(AccionObserver::class)]
class Accion extends Model
{
    protected $table = 'acciones';
    /** @use HasFactory<\Database\Factories\AccionFactory> */
    use HasFactory;

    protected $fillable = [
        'accion',
        'icono',
        'slug',
        'order'
    ];
}
