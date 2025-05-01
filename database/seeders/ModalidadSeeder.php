<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModalidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modalidades = [
            ['nombre' => 'ESCOLARIZADA'],
            ['nombre' => 'SEMIESCOLARIZADA'],
        ];

        foreach ($modalidades as $modalidad) {
            \App\Models\Modalidad::create($modalidad);
        }
    }
}
