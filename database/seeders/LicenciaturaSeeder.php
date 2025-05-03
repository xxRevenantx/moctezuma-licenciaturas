<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LicenciaturaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $licenciaturas = [
            [
                'nombre' => 'Nutrición',
                'RVOE' => NULL,
                'nombre_corto' => 'Nutrición',
                'imagen' => NULL,
                'slug' => 'nutricion',
            ],
            [
                'nombre' => 'Administración Empresarial',
                'RVOE' => 'SEG/0011/2021',
                'nombre_corto' => 'Administración Emp.',
                'imagen' => NULL,
                'slug' => 'administracion-empresarial',
            ],
            [
                'nombre' => 'Ciencias Políticas y Administración Pública',
                'RVOE' => NULL,
                'nombre_corto' => 'Ciencias Políticas',
                'imagen' => NULL,
                'slug' => 'ciencias-politicas-y-administracion-publica',
            ],
            [
                'nombre' => 'Criminalística, Criminología y Técnicas Periciales',
                'RVOE' => 'SEG/032/2021',
                'nombre_corto' => 'Criminalística',
                'imagen' => NULL,
                'slug' => 'criminologia-criminalista-y-tecnicas-periciales',
            ],
            [
                'nombre' => 'Ciencias de la Educación',
                'RVOE' => 'SEG/102/2022',
                'nombre_corto' => 'Ciencias de la Educación',
                'imagen' => NULL,
                'slug' => 'ciencias-de-la-educacion',
            ],
            [
                'nombre' => 'Cultura Física y Deportes',
                'RVOE' => 'SEG/101/2022',
                'nombre_corto' => 'Cultura Física y Deportes',
                'imagen' => NULL,
                'slug' => 'cultura-fisica-y-deportes',
            ],
            [
                'nombre' => 'Contaduría Pública',
                'RVOE' => NULL,
                'nombre_corto' => 'Contaduría Pública',
                'imagen' => NULL,
                'slug' => 'contabilidad-publica',
            ],
            [
                'nombre' => 'Arquitectura y Diseño de Interiores',
                'RVOE' => NULL,
                'nombre_corto' => 'Arquitectura',
                'imagen' => NULL,
                'slug' => 'arquitectura-y-diseno-de-interiores',
            ],
        ];

        foreach ($licenciaturas as $licenciatura) {
            \App\Models\Licenciatura::create($licenciatura);
        }

    }
}
