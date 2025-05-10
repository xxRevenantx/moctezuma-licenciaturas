<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CuatrimestreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cuatrimestres = [
            ['cuatrimestre' => '1', 'nombre_cuatrimestre' => "1° CUATRIMESTRE"],
            ['cuatrimestre' => '2', 'nombre_cuatrimestre' => "2° CUATRIMESTRE"],
            ['cuatrimestre' => '3', 'nombre_cuatrimestre' => "3° CUATRIMESTRE"],
            ['cuatrimestre' => '4', 'nombre_cuatrimestre' => "4° CUATRIMESTRE"],
            ['cuatrimestre' => '5', 'nombre_cuatrimestre' => "5° CUATRIMESTRE"],
            ['cuatrimestre' => '6', 'nombre_cuatrimestre' => "6° CUATRIMESTRE"],
            ['cuatrimestre' => '7', 'nombre_cuatrimestre' => "7° CUATRIMESTRE"],
            ['cuatrimestre' => '8', 'nombre_cuatrimestre' => "8° CUATRIMESTRE"],
            ['cuatrimestre' => '9', 'nombre_cuatrimestre' => "9° CUATRIMESTRE"],

        ];

        foreach ($cuatrimestres as $cuatrimestre) {
            \App\Models\Cuatrimestre::create($cuatrimestre);
        }

    }
}
