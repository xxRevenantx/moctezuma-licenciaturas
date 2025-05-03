<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meses = [
            [
                "meses" => "SEPTIEMBRE/DICIEMBRE",
                'meses_corto' => "SEP/DIC"
            ],
            [
                "meses" => "ENERO/ABRIL",
                'meses_corto' => "ENE/ABR"
            ],
            [
                "meses" => "MAYO/AGOSTO",
                'meses_corto' => "MAY/AGO"
            ],

        ];

        foreach ($meses as $mes) {
            \App\Models\Mes::create($mes);
        }
    }
}
