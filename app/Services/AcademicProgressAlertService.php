<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Diagnóstico no invasivo de avance académico.
 * El cuatrimestre esperado es una sugerencia de calendario, no una promoción automática.
 */
class AcademicProgressAlertService
{
    public function evaluar(array $filtros = []): array
    {
        $hoy = now()->toDateString();
        $hasta = now()->addDays(15)->toDateString();
        $salida = [
            'pendientes' => [], 'proximos' => [], 'justificados' => [],
            'conflictos' => [], 'sin_periodo' => [],
            'total_pendientes' => 0, 'total_proximos' => 0, 'total_justificados' => 0,
            'total_criticos' => 0, 'fecha_corte' => $hoy,
        ];
        if (! Schema::hasTable('periodos') || ! Schema::hasTable('inscripciones')) {
            return $salida;
        }

        $periodos = DB::table('periodos as p')
            ->join('generaciones as g', 'g.id', '=', 'p.generacion_id')
            ->join('cuatrimestres as c', 'c.id', '=', 'p.cuatrimestre_id')
            ->where('g.activa', 'true')
            ->whereNotNull('p.inicio_periodo')
            ->whereNotNull('p.termino_periodo')
            ->whereDate('p.inicio_periodo', '<=', $hasta)
            ->whereDate('p.termino_periodo', '>=', $hoy)
            ->whereIn('c.cuatrimestre', array_map('strval', range(1, 9)))
            ->get(['p.id', 'p.generacion_id', 'p.cuatrimestre_id', 'p.inicio_periodo', 'p.termino_periodo', 'c.cuatrimestre']);

        $actuales = [];
        $futuros = [];
        foreach ($periodos as $p) {
            $id = (int) $p->generacion_id;
            if ($p->inicio_periodo <= $hoy) {
                $actuales[$id][] = $p;
            } else {
                $futuros[$id][] = $p;
            }
        }
        $vigentes = [];
        foreach ($actuales as $id => $lista) {
            if (count($lista) === 1) {
                $vigentes[$id] = $lista[0];
            } else {
                $salida['conflictos'][] = ['generacion_id' => $id, 'mensaje' => 'Hay periodos simultáneos en esta generación; revisar calendario antes de promover.'];
            }
        }
        $siguientes = [];
        foreach ($futuros as $id => $lista) {
            usort($lista, fn ($a, $b) => strcmp($a->inicio_periodo, $b->inicio_periodo));
            // Dos periodos inician el mismo día: no inferir una promoción ambigua.
            if (count($lista) > 1 && $lista[0]->inicio_periodo === $lista[1]->inicio_periodo) {
                $salida['conflictos'][] = ['generacion_id' => $id, 'mensaje' => 'Hay próximos periodos ambiguos en el calendario.'];
                continue;
            }
            $siguientes[$id] = $lista[0];
        }

        $query = DB::table('inscripciones as i')
            ->join('generaciones as g', 'g.id', '=', 'i.generacion_id')
            ->join('licenciaturas as l', 'l.id', '=', 'i.licenciatura_id')
            ->join('modalidades as m', 'm.id', '=', 'i.modalidad_id')
            ->join('cuatrimestres as c', 'c.id', '=', 'i.cuatrimestre_id')
            ->where('g.activa', 'true')
            ->where('i.status', 'true')
            ->where('i.egresado', 'false')
            ->whereNull('i.fecha_baja')
            ->select([
                'i.id', 'i.generacion_id', 'i.licenciatura_id', 'i.modalidad_id',
                'i.cuatrimestre_id', 'i.nombre', 'i.apellido_paterno', 'i.apellido_materno',
                'i.matricula', 'i.matricula_interna', 'g.generacion', 'l.nombre as licenciatura',
                'm.nombre as modalidad', 'c.cuatrimestre as cuatrimestre_actual',
            ]);
        foreach (['licenciatura', 'modalidad', 'generacion'] as $campo) {
            $ids = $filtros[$campo.'_ids'] ?? null;
            if ($ids !== null) {
                $query->whereIn('i.'.$campo.'_id', $ids);
            } elseif (! empty($filtros[$campo.'_id'])) {
                $query->where('i.'.$campo.'_id', (int) $filtros[$campo.'_id']);
            }
        }
        $registros = $query->orderBy('l.nombre')->orderBy('i.apellido_paterno')->orderBy('i.nombre')->get();
        $excepciones = collect();
        if (Schema::hasTable('avance_academico_excepciones')) {
            $excepciones = DB::table('avance_academico_excepciones')
                ->whereIn('inscripcion_id', $registros->pluck('id'))
                ->get()->keyBy(fn ($r) => $r->inscripcion_id.'-'.$r->periodo_id);
        }

        foreach ($registros as $alumno) {
            $gid = (int) $alumno->generacion_id;
            // Durante el receso también se anticipa el próximo cambio (sin promover).
            if (! isset($vigentes[$gid])) {
                if (isset($siguientes[$gid]) && (int) $alumno->cuatrimestre_actual < (int) $siguientes[$gid]->cuatrimestre) {
                    $salida['proximos'][] = [
                        'id' => (int) $alumno->id,
                        'nombre' => trim($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre),
                        'generacion' => $alumno->generacion,
                        'cuatrimestre_actual' => (int) $alumno->cuatrimestre_actual,
                        'proximo_cuatrimestre' => (int) $siguientes[$gid]->cuatrimestre,
                        'inicio_proximo' => $siguientes[$gid]->inicio_periodo,
                    ];
                }
                continue;
            }
            $periodo = $vigentes[$gid];
            $actual = (int) $alumno->cuatrimestre_actual;
            $esperado = (int) $periodo->cuatrimestre;
            if ($actual < 1 || $actual > 9 || $esperado < 1 || $esperado > 9) {
                continue;
            }
            $base = [
                'id' => (int) $alumno->id, 'nombre' => trim($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre),
                'matricula' => $alumno->matricula ?: $alumno->matricula_interna ?: 'ID '.$alumno->id,
                'licenciatura' => $alumno->licenciatura, 'modalidad' => $alumno->modalidad,
                'generacion' => $alumno->generacion, 'generacion_id' => $gid,
                'licenciatura_id' => (int) $alumno->licenciatura_id, 'modalidad_id' => (int) $alumno->modalidad_id,
                'cuatrimestre_actual' => $actual, 'cuatrimestre_id' => (int) $alumno->cuatrimestre_id,
                'cuatrimestre_esperado' => $esperado, 'destino_id' => (int) $periodo->cuatrimestre_id,
                'periodo_id' => (int) $periodo->id, 'inicio_periodo' => $periodo->inicio_periodo,
                'termino_periodo' => $periodo->termino_periodo,
            ];
            if ($actual < $esperado) {
                $clave = $alumno->id.'-'.$periodo->id;
                if ($excepciones->has($clave)) {
                    $ex = $excepciones->get($clave);
                    $salida['justificados'][] = [...$base, 'motivo' => $ex->motivo];
                } else {
                    $dias = Carbon::parse($periodo->inicio_periodo)->diffInDays(Carbon::parse($hoy), false);
                    $salida['pendientes'][] = [...$base, 'dias_desfase' => (int) $dias,
                        'prioridad' => $dias >= 7 ? 'alta' : 'normal'];
                    if ($dias >= 7) {
                        $salida['total_criticos']++;
                    }
                }
            } elseif (isset($siguientes[$gid])) {
                $siguiente = $siguientes[$gid];
                if ((int) $siguiente->cuatrimestre > $actual) {
                    $salida['proximos'][] = [...$base, 'proximo_cuatrimestre' => (int) $siguiente->cuatrimestre,
                        'inicio_proximo' => $siguiente->inicio_periodo];
                }
            }
        }
        $salida['total_pendientes'] = count($salida['pendientes']);
        $salida['total_proximos'] = count($salida['proximos']);
        $salida['total_justificados'] = count($salida['justificados']);

        return $salida;
    }
}
