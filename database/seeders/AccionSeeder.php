<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $acciones = [
            [
                ['accion' => 'Inscripciones', 'icono' => 'inscripcion.png', 'slug' => 'inscripcion', 'order' => 1],
                ['accion' => 'Matrícula', 'icono' => 'alumnos.png', 'slug' => 'matricula', 'order' => 2],
                ['accion' => 'Asignación de materias', 'icono' => 'materias.png', 'slug' => 'asignacion-de-materias', 'order' => 3],
                ['accion' => 'Horarios', 'icono' => 'horarios.png', 'slug' => 'horarios', 'order' => 4],
                ['accion' => 'Calificaciones', 'icono' => 'calificaciones.png', 'slug' => 'calificaciones', 'order' => 5],
                ['accion' => 'Bajas', 'icono' => 'baja.png', 'slug' => 'bajas', 'order' => 6],
            ]
        ];
        foreach ($acciones as $accion) {
            foreach ($accion as $data) {
                \App\Models\Accion::create($data);
            }
        }
    }
}
