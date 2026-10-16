<?php

namespace Tests\Unit;

use App\Services\GeneradorListasService;
use PHPUnit\Framework\TestCase;

class GeneradorListasServiceTest extends TestCase
{
    public function test_modalidad_impresa_estatica_no_cambia_alumno(): void
    {
        $alumno = (object) ['id' => 10, 'modalidad_nombre' => 'Semiescolarizada'];
        $servicio = new GeneradorListasService();
        $metodo = new \ReflectionMethod($servicio, 'valor');

        self::assertSame('ESCOLARIZADA', $metodo->invoke($servicio, $alumno, 'modalidad', 1, ['modalidad_texto' => 'escolarizada']));
        self::assertSame('SEMIESCOLARIZADA', $metodo->invoke($servicio, $alumno, 'modalidad', 1, ['modalidad_texto' => 'semiescolarizada']));
        self::assertSame('SEMIESCOLARIZADA', $metodo->invoke($servicio, $alumno, 'modalidad', 1, ['modalidad_texto' => 'real']));
        self::assertSame('Semiescolarizada', $alumno->modalidad_nombre);
    }

    public function test_cabeceras_seleccionadas_en_el_orden_requerido(): void
    {
        $servicio = new GeneradorListasService();
        self::assertSame(['Apellidos', 'Matrícula'], $servicio->cabeceras([
            'columnas' => ['apellido_paterno', 'matricula'],
            'cabeceras' => ['apellido_paterno' => 'Apellidos', 'matricula' => ''],
        ]));
    }

    public function test_las_columnas_por_defecto_tienen_campos_registrados(): void
    {
        self::assertSame([], array_diff(GeneradorListasService::DEFAULT_COLUMNAS, array_keys(GeneradorListasService::CAMPOS)));
    }
}
