<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModalidadeSeeder extends Seeder
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
            \App\Models\Modalidade::create($modalidad);
        }
    }
}
