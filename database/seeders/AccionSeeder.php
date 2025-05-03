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
                ['accion' => 'Inscribir alumnos', 'icono' => 'inscripcion.png', 'slug' => 'inscribir-alumnos', 'order' => 1],
                ['accion' => 'Ver alumnos', 'icono' => 'alumnos.png', 'slug' => 'ver-alumnos', 'order' => 2],
                ['accion' => 'Asignación de materias', 'icono' => 'materias.png', 'slug' => 'asignacion-de-materias', 'order' => 3],
                ['accion' => 'Horarios', 'icono' => 'horarios.png', 'slug' => 'horarios', 'order' => 4],
                ['accion' => 'Calificaciones', 'icono' => 'calificaciones.png', 'slug' => 'calificaciones', 'order' => 5],
                ['accion' => 'Documentos', 'icono' => 'documentos.png', 'slug' => 'documentos', 'order' => 6],
                ['accion' => 'Bajas', 'icono' => 'baja.png', 'slug' => 'bajas', 'order' => 7],
            ]
        ];
        foreach ($acciones as $accion) {
            foreach ($accion as $data) {
                \App\Models\Accion::create($data);
            }
        }
    }
}
