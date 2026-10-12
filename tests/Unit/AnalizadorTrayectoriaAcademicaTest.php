<?php

namespace Tests\Unit;

use App\Services\Academico\AnalizadorTrayectoriaAcademica as Analizador;
use PHPUnit\Framework\TestCase;

class AnalizadorTrayectoriaAcademicaTest extends TestCase
{
    private function periodo(int $numero, array $notas, int $esperadas = 5): array
    {
        return ['cuatrimestre' => $numero, 'esperadas' => $esperadas, 'notas' => $notas];
    }

    public function test_inexistencia_de_historial_no_genera_riesgo_falso(): void
    {
        $r = Analizador::evaluarPeriodos([]);
        $this->assertSame('sin_datos', $r['nivel']);
        $this->assertSame('sin_datos', $r['tendencia']);
    }

    public function test_no_utiliza_periodos_con_cobertura_insuficiente(): void
    {
        $r = Analizador::evaluarPeriodos([$this->periodo(2, ['5', 'NP'], 6)]);
        $this->assertSame('sin_datos', $r['nivel']);
        $this->assertFalse($r['periodos'][0]['suficiente']);
    }

    public function test_np_y_reprobaciones_priorizan_seguimiento_sin_promediar_np(): void
    {
        $r = Analizador::evaluarPeriodos([$this->periodo(3, ['NP', '5', '5', '7', '8'])]);
        $this->assertSame('alto', $r['nivel']);
        $this->assertSame(1, $r['periodos'][0]['np']);
        $this->assertSame(2, $r['periodos'][0]['reprobadas']);
        $this->assertEquals(6.25, $r['ultimo_promedio']); // solo media numérica descriptiva
    }

    public function test_detecta_descenso_entre_periodos_consecutivos(): void
    {
        $r = Analizador::evaluarPeriodos([
            $this->periodo(4, [9, 9, 9, 9, 9]),
            $this->periodo(5, [7, 7, 7, 7, 7]),
        ]);
        $this->assertSame('descendente', $r['tendencia']);
        $this->assertSame(-2.0, $r['cambio']);
        $this->assertSame('alto', $r['nivel']);
    }

    public function test_no_compara_cuatrimestres_no_consecutivos(): void
    {
        $r = Analizador::evaluarPeriodos([
            $this->periodo(1, [9, 9, 9, 9, 9]),
            $this->periodo(3, [7, 7, 7, 7, 7]),
        ]);
        $this->assertSame('sin_datos', $r['tendencia']);
        $this->assertNull($r['cambio']);
        $this->assertSame('medio', $r['nivel']);
    }

    public function test_una_trayectoria_estable_no_es_riesgo(): void
    {
        $r = Analizador::evaluarPeriodos([
            $this->periodo(1, [9, 9, 9, 9, 9]),
            $this->periodo(2, [9, 9.5, 9, 9, 9]),
        ]);
        $this->assertSame('bajo', $r['nivel']);
        $this->assertSame('estable', $r['tendencia']);
    }

    public function test_reprobacion_en_periodos_consecutivos_eleva_prioridad(): void
    {
        $r = Analizador::evaluarPeriodos([
            $this->periodo(3, [5, 8, 8, 8, 8]),
            $this->periodo(4, [5, 8, 8, 8, 8]),
        ], 5);
        $this->assertSame('alto', $r['nivel']);
        $this->assertContains('Materias reprobadas en dos cuatrimestres consecutivos', $r['motivos']);
    }

    public function test_prioridad_no_se_basara_en_evidencia_demasiado_antigua(): void
    {
        $r = Analizador::evaluarPeriodos([
            $this->periodo(2, [5, 5, 5, 8, 8]),
        ], 8);
        $this->assertSame('sin_datos', $r['nivel']);
        $this->assertSame('sin_datos', $r['tendencia']);
    }

    public function test_descenso_sostenido_durante_tres_periodos_se_prioriza(): void
    {
        $r = Analizador::evaluarPeriodos([
            $this->periodo(3, [9.5, 9.5, 9.5, 9.5, 9.5]),
            $this->periodo(4, [9.0, 9.0, 9.0, 9.0, 9.0]),
            $this->periodo(5, [8.5, 8.5, 8.5, 8.5, 8.5]),
        ], 6);
        $this->assertSame('alto', $r['nivel']);
        $this->assertContains('Descenso sostenido durante tres cuatrimestres consecutivos', $r['motivos']);
    }

    public function test_np_no_se_interpreta_como_cero_ni_crea_descenso_artificial(): void
    {
        $r = Analizador::evaluarPeriodos([
            $this->periodo(1, [9, 9, 9, 9, 9]),
            $this->periodo(2, ['NP', 'NP', 'NP', 'NP', 'NP']),
        ]);
        $this->assertSame('alto', $r['nivel']);
        $this->assertNull($r['ultimo_promedio']);
        $this->assertNull($r['cambio']);
    }
}
