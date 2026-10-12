<?php

namespace App\Services\Academico;

use Illuminate\Support\Facades\DB;

/**
 * Prioriza el seguimiento por REGLAS EXPLICABLES, no por un modelo entrenado.
 * Solo consulta calificaciones persistidas de cuatrimestres ANTERIORES.
 * No modifica calificaciones, inscripciones ni estatus de los estudiantes.
 */
final class AnalizadorTrayectoriaAcademica
{
    /**
     * @param array<int, int> $alumnoIds
     * @return array<int, array<string, mixed>> Identificador de inscripción => evaluación
     */
    public function analizar(array $alumnoIds, int $licenciaturaId, int $generacionId, int $cuatrimestreActual): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $alumnoIds), fn ($id) => $id > 0)));
        if (!$ids || $cuatrimestreActual < 2) {
            return array_fill_keys($ids, self::evaluarPeriodos([]));
        }

        // Solamente periodos definidos para la generación seleccionada; el actual no participa.
        $anteriores = DB::table('periodos')
            ->where('generacion_id', $generacionId)
            ->where('cuatrimestre_id', '<', $cuatrimestreActual)
            ->whereNotNull('cuatrimestre_id')
            ->distinct()->pluck('cuatrimestre_id')->map(fn ($id) => (int) $id)->all();
        if (!$anteriores) {
            return array_fill_keys($ids, self::evaluarPeriodos([]));
        }

        // Conteo del plan por cuatrimestre y modalidad (evita penalizar materias no asignadas).
        $esperadas = DB::table('asignacion_materias as am')
            ->join('materias as m', 'm.id', '=', 'am.materia_id')
            ->where('am.licenciatura_id', $licenciaturaId)
            ->whereIn('am.cuatrimestre_id', $anteriores)
            ->where('m.calificable', 'true')
            ->select('am.cuatrimestre_id', 'am.modalidad_id')
            ->selectRaw('COUNT(DISTINCT am.materia_id) AS cantidad')
            ->groupBy('am.cuatrimestre_id', 'am.modalidad_id')->get();
        $mapaEsperadas = [];
        foreach ($esperadas as $fila) {
            $mapaEsperadas[(int) $fila->cuatrimestre_id][(int) $fila->modalidad_id] = (int) $fila->cantidad;
        }

        // No filtrar por modalidad: al cambiar de modalidad pueden existir notas previas
        // válidas que deben preservarse. Cada cuatrimestre usa su modalidad histórica.
        // No incluir notas de otra licenciatura, generación o ciclo posterior.
        $notas = DB::table('calificaciones as c')
            ->join('asignacion_materias as am', 'am.id', '=', 'c.asignacion_materia_id')
            ->join('materias as m', 'm.id', '=', 'am.materia_id')
            ->whereIn('c.alumno_id', $ids)
            ->whereIn('c.cuatrimestre_id', $anteriores)
            ->where('c.licenciatura_id', $licenciaturaId)
            ->where('c.generacion_id', $generacionId)
            ->whereColumn('am.cuatrimestre_id', 'c.cuatrimestre_id')
            ->whereColumn('am.licenciatura_id', 'c.licenciatura_id')
            ->whereColumn('am.modalidad_id', 'c.modalidad_id')
            ->where('m.calificable', 'true')
            ->select('c.id', 'c.alumno_id', 'c.cuatrimestre_id', 'c.modalidad_id', 'am.materia_id', 'c.calificacion')
            ->orderBy('c.id')->get();

        // [alumno][cuatrimestre][modalidad][materia] => última nota del contexto
        $agrupadas = [];
        foreach ($notas as $nota) {
            $valor = self::normalizar($nota->calificacion);
            if ($valor === null) continue;
            $agrupadas[(int) $nota->alumno_id][(int) $nota->cuatrimestre_id][(int) $nota->modalidad_id][(int) $nota->materia_id] = $valor;
        }

        $resultado = [];
        foreach ($ids as $id) {
            $periodos = [];
            foreach ($agrupadas[$id] ?? [] as $cuatri => $modalidades) {
                // Cuando existe una transferencia, tomar la modalidad más representativa
                // de ese periodo sin duplicar materias por reasignaciones.
                $modalidadElegida = null;
                $cantidadElegida = -1;
                foreach ($modalidades as $modalidad => $materias) {
                    $cantidad = count($materias);
                    if ($cantidad > $cantidadElegida) {
                        $cantidadElegida = $cantidad;
                        $modalidadElegida = (int) $modalidad;
                    }
                }
                if ($modalidadElegida === null) continue;
                $periodos[] = [
                    'cuatrimestre' => (int) $cuatri,
                    'modalidad' => $modalidadElegida,
                    'esperadas' => $mapaEsperadas[$cuatri][$modalidadElegida] ?? 0,
                    'notas' => array_values($modalidades[$modalidadElegida]),
                ];
            }
            $resultado[$id] = self::evaluarPeriodos($periodos, $cuatrimestreActual);
        }
        return $resultado;
    }

    /**
     * Motor determinista, separado del acceso SQL para facilitar pruebas.
     * Periodos: cuatrimestre, esperadas, notas: (5..10 o NP).
     * No extrapola porcentajes de probabilidad ni predice calificaciones concretas.
     */
    public static function evaluarPeriodos(array $periodos, ?int $cuatrimestreActual = null): array
    {
        usort($periodos, fn ($a, $b) => (int) $a['cuatrimestre'] <=> (int) $b['cuatrimestre']);
        $historial = [];
        foreach ($periodos as $periodo) {
            $esperadas = max(0, (int) ($periodo['esperadas'] ?? 0));
            $numericCount = $suma = $reprobadas = $np = $capturadas = 0;
            foreach ($periodo['notas'] ?? [] as $nota) {
                $valor = self::normalizar($nota);
                if ($valor === null) continue;
                $capturadas++;
                if ($valor === 'NP') {
                    $np++;
                    continue;
                }
                $numero = (float) $valor;
                $suma += $numero;
                $numericCount++;
                if ($numero < 6) $reprobadas++;
            }
            $cobertura = $esperadas ? (int) round(min($capturadas, $esperadas) * 100 / $esperadas) : 0;
            // Asegura muestras comparables. Una materia aislada no justifica una alerta.
            $suficiente = $esperadas > 0
                && $capturadas >= min(3, $esperadas)
                && $cobertura >= 70;
            $historial[] = [
                'cuatrimestre' => (int) ($periodo['cuatrimestre'] ?? 0),
                'esperadas' => $esperadas,
                'capturadas' => $capturadas,
                'cobertura' => $cobertura,
                'reprobadas' => $reprobadas,
                'np' => $np,
                // Promedio de notas numéricas; NO es el promedio oficial si existe NP.
                'promedio_numerico' => $numericCount ? round($suma / $numericCount, 2) : null,
                'suficiente' => $suficiente,
            ];
        }

        $validos = array_values(array_filter($historial, fn ($p) => $p['suficiente']));
        $ultimo = count($validos) ? $validos[count($validos) - 1] : null;
        $previo = count($validos) > 1 ? $validos[count($validos) - 2] : null;
        $tendencia = 'sin_datos';
        $cambio = null;
        $ultimoEvaluableReciente = !$ultimo || $cuatrimestreActual === null
            || $ultimo['cuatrimestre'] >= $cuatrimestreActual - 2;
        $antepenultimo = count($validos) >= 3 ? $validos[count($validos) - 3] : null;
        // Solo comparar periodos consecutivos con cobertura suficiente.
        if ($ultimo && $previo
            && $ultimo['cuatrimestre'] === $previo['cuatrimestre'] + 1
            && $ultimo['promedio_numerico'] !== null && $previo['promedio_numerico'] !== null
        ) {
            $cambio = round($ultimo['promedio_numerico'] - $previo['promedio_numerico'], 2);
            $tendencia = $cambio <= -0.75 ? 'descendente' : ($cambio >= 0.75 ? 'ascendente' : 'estable');
        }

        $nivel = 'sin_datos';
        $motivos = [];
        if ($ultimo) {
            $nivel = 'bajo';
            if ($ultimo['np'] >= 1) $motivos[] = "{$ultimo['np']} materia(s) con NP en el último periodo evaluable";
            if ($ultimo['reprobadas'] >= 1) $motivos[] = "{$ultimo['reprobadas']} materia(s) reprobada(s) en el último periodo evaluable";
            if ($ultimo['promedio_numerico'] !== null && $ultimo['promedio_numerico'] < 8) {
                $motivos[] = 'Media de notas numéricas inferior a 8 en el último periodo evaluable';
            }
            if ($cambio !== null && $cambio <= -0.75) {
                $motivos[] = 'Descenso de '.number_format(abs($cambio), 2).' puntos respecto al periodo consecutivo anterior';
            }
            $reprobacionRepetida = $previo && $ultimo['cuatrimestre'] === $previo['cuatrimestre'] + 1
                && $ultimo['reprobadas'] > 0 && $previo['reprobadas'] > 0;
            if ($reprobacionRepetida) {
                $motivos[] = 'Materias reprobadas en dos cuatrimestres consecutivos';
            }
            $descensoSostenido = $antepenultimo
                && $previo['cuatrimestre'] === $antepenultimo['cuatrimestre'] + 1
                && $ultimo['cuatrimestre'] === $previo['cuatrimestre'] + 1
                && $antepenultimo['promedio_numerico'] !== null
                && $previo['promedio_numerico'] !== null
                && $ultimo['promedio_numerico'] !== null
                && ($previo['promedio_numerico'] - $antepenultimo['promedio_numerico']) <= -0.5
                && ($ultimo['promedio_numerico'] - $previo['promedio_numerico']) <= -0.5;
            if ($descensoSostenido) {
                $tendencia = 'descendente';
                $motivos[] = 'Descenso sostenido durante tres cuatrimestres consecutivos';
            }
            if ($ultimo['np'] >= 1 || $ultimo['reprobadas'] >= 2
                || $reprobacionRepetida || $descensoSostenido
                || ($ultimo['promedio_numerico'] !== null && $ultimo['promedio_numerico'] < 6.5)
                || ($cambio !== null && $cambio <= -1.5 && $ultimo['promedio_numerico'] < 8)
            ) {
                $nivel = 'alto';
            } elseif ($ultimo['reprobadas'] >= 1
                || ($ultimo['promedio_numerico'] !== null && $ultimo['promedio_numerico'] < 8)
                || ($cambio !== null && $cambio <= -0.75)
            ) {
                $nivel = 'medio';
            }
            if (!$motivos) $motivos[] = 'Sin señales de bajo rendimiento según los criterios del análisis histórico';
            if (!$ultimoEvaluableReciente) {
                // Evitar convertir calificaciones demasiado antiguas en una alerta presente.
                $nivel = 'sin_datos';
                $tendencia = 'sin_datos';
                $cambio = null;
                $motivos = [
                    'Último periodo evaluable: '.$ultimo['cuatrimestre'].'.º; faltan datos recientes suficientes para priorizar este cuatrimestre',
                ];
            }
        } else {
            $motivos[] = 'No hay cuatrimestres anteriores con suficientes calificaciones registradas';
        }
        $accion = match ($nivel) {
            'alto' => 'Priorizar revisión del expediente y contactar al alumno para acordar un seguimiento académico.',
            'medio' => 'Programar revisión preventiva y verificar materias que requieren apoyo.',
            'bajo' => 'Mantener seguimiento ordinario y revisar el próximo cierre de calificaciones.',
            default => 'Completar o verificar la captura histórica antes de estimar una prioridad.',
        };
        $evidencia = count($validos) >= 3 ? 'amplia' : (count($validos) === 2 ? 'moderada' : (count($validos) === 1 ? 'limitada' : 'insuficiente'));
        return [
            'nivel' => $nivel,
            'evidencia' => $evidencia,
            'tendencia' => $tendencia,
            'cambio' => $cambio,
            'ultimo_promedio' => $ultimo['promedio_numerico'] ?? null,
            'ultimo_cuatrimestre' => $ultimo['cuatrimestre'] ?? null,
            'periodos_evaluables' => count($validos),
            'motivos' => $motivos,
            'accion' => $accion,
            'periodos' => array_slice($historial, -4),
        ];
    }

    private static function normalizar(mixed $nota): ?string
    {
        if ($nota === null || trim((string) $nota) === '') return null;
        if (strtoupper(trim((string) $nota)) === 'NP') return 'NP';
        if (!is_numeric($nota)) return null;
        $numero = (float) $nota;
        if (!is_finite($numero) || $numero < 5 || $numero > 10) return null;
        return (string) $numero;
    }
}
