<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{

    protected $table = 'profesores';

    protected $fillable = [
        'user_id',
        'foto',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'perfil',
        'color',
        'status'
    ];

    /** @use HasFactory<\Database\Factories\ProfesorFactory> */
    use HasFactory;


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function asignacionMaterias()
    {
        return $this->hasMany(AsignacionMateria::class);
    }

}
