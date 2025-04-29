<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    /** @use HasFactory<\Database\Factories\DashboardFactory> */
    use HasFactory;

    protected $fillable = [
        'ciclo_escolar',
        'periodo_escolar',
    ];
}
