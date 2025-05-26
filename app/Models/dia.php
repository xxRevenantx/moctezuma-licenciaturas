<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dia extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'dias';

    protected $fillable = [
        'dia',
    ];

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

}
