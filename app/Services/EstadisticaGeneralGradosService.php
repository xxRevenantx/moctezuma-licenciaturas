<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Estadística institucional de matrícula vigente por GRADO (3 cuatrimestres).
 * Sólo lee datos; nunca modifica inscripciones, generaciones o periodos.
 */
class EstadisticaGeneralGradosService
{
    public function normalizarFiltros(array $datos): array
    {
        return [
            // Se conservan los filtros individuales para enlaces históricos.
            'licenciatura_ids' => $this->normalizarIds($datos, 'licenciatura_ids', 'licenciatura_id'),
            'modalidad_ids' => $this->normalizarIds($datos, 'modalidad_ids', 'modalidad_id'),
            'generacion_ids' => $this->normalizarIds($datos, 'generacion_ids', 'generacion_id'),
            'mostrar_grados_vacios' => filter_var($datos['mostrar_grados_vacios'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * null = todas; [] = ninguna; [1, 2] = sólo esas opciones.
     * Acepta tanto URL CSV como arrays de Livewire o parámetros tradicionales.
     */
    private function normalizarIds(array $datos, string $plural, string $singular): ?array
    {
        if (array_key_exists($plural, $datos)) {
            $entrada = $datos[$plural];
        } elseif (array_key_exists($singular, $datos)) {
            $entrada = $datos[$singular];
        } else {
            return null;
        }

        if ($entrada === null || $entrada === '') {
            return null;
        }
        if ($entrada === 'none') {
            return [];
        }

        $valores = is_array($entrada) ? $entrada : explode(',', (string) $entrada);
        $ids = [];
        foreach ($valores as $valor) {
            if (! is_scalar($valor) || ! preg_match('/^[1-9][0-9]*$/D', (string) $valor)) {
                continue;
            }
            $ids[] = (int) $valor;
        }

        return array_values(array_unique($ids));
    }

    /**
     * Vigencia por calendario académico, no sólo por g.activa: las generaciones
     * antiguas de la BD pueden conservar activa='true' después de graduarse.
     */
    private function periodoVigente(Builder $query, string $hoy): Builder
    {
        return $query->whereExists(function (Builder $periodo) use ($hoy) {
            $periodo->selectRaw('1')
                ->from('periodos as pv')
                ->whereColumn('pv.generacion_id', 'g.id')
                ->whereDate('pv.inicio_periodo', '<=', $hoy)
                ->whereDate('pv.termino_periodo', '>=', $hoy);
        });
    }

    public function generacionesVigentes(): array
    {
        $hoy = now()->toDateString();

        return $this->periodoVigente(DB::table('generaciones as g')->where('g.activa', 'true'), $hoy)
            ->orderBy('g.generacion')
            ->get(['g.id', 'g.generacion'])
            ->map(fn ($g) => ['id' => (int) $g->id, 'generacion' => $g->generacion])
            ->all();
    }

    public function generar(array $datos = []): array
    {
        $filtros = $this->normalizarFiltros($datos);
        $hoy = now()->toDateString();
        $query = DB::table('inscripciones as i')
            ->join('licenciaturas as l', 'l.id', '=', 'i.licenciatura_id')
            ->join('generaciones as g', 'g.id', '=', 'i.generacion_id')
            ->join('cuatrimestres as c', 'c.id', '=', 'i.cuatrimestre_id')
            ->where('g.activa', 'true')
            ->where('i.status', 'true')
            ->whereNull('i.fecha_baja')
            ->where('i.egresado', 'false')
            ->whereIn('c.cuatrimestre', array_map('strval', range(1, 9)))
            ->when($filtros['licenciatura_ids'] !== null, fn ($q) => $q->whereIn('i.licenciatura_id', $filtros['licenciatura_ids']))
            ->when($filtros['modalidad_ids'] !== null, fn ($q) => $q->whereIn('i.modalidad_id', $filtros['modalidad_ids']))
            ->when($filtros['generacion_ids'] !== null, fn ($q) => $q->whereIn('i.generacion_id', $filtros['generacion_ids']));

        $query = $this->periodoVigente($query, $hoy);

        // Coherencia con EstadisticaLicenciaturasService: no contar un alumno
        // aún marcado activo si ya concluyó su noveno cuatrimestre.
        $query->where(function (Builder $q) use ($hoy) {
            $q->where('c.cuatrimestre', '!=', '9')
                ->orWhereNotExists(function (Builder $finalizado) use ($hoy) {
                    $finalizado->selectRaw('1')
                        ->from('periodos as p9')
                        ->whereColumn('p9.generacion_id', 'i.generacion_id')
                        ->whereColumn('p9.cuatrimestre_id', 'i.cuatrimestre_id')
                        ->whereDate('p9.termino_periodo', '<', $hoy);
                });
        });

        // Se agrupa por cuatrimestre real, y el grado se calcula después:
        // no se asume que id de cuatrimestres coincida con su número.
        $filas = $query
            ->select('l.id as licenciatura_id', 'l.nombre as licenciatura', 'c.cuatrimestre')
            ->selectRaw("SUM(CASE WHEN i.sexo = 'H' THEN 1 ELSE 0 END) AS hombres")
            ->selectRaw("SUM(CASE WHEN i.sexo = 'M' THEN 1 ELSE 0 END) AS mujeres")
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('l.id', 'l.nombre', 'c.cuatrimestre')
            ->orderBy('l.nombre')
            ->get();

        $licenciaturas = [];
        $totales = ['hombres' => 0, 'mujeres' => 0, 'total' => 0];

        foreach ($filas as $fila) {
            $id = (int) $fila->licenciatura_id;
            $grado = intdiv(((int) $fila->cuatrimestre) - 1, 3) + 1;

            if (! isset($licenciaturas[$id])) {
                $licenciaturas[$id] = [
                    'id' => $id,
                    'nombre' => $fila->licenciatura,
                    'grados' => [],
                    'totales' => ['hombres' => 0, 'mujeres' => 0, 'total' => 0],
                ];
            }
            if (! isset($licenciaturas[$id]['grados'][$grado])) {
                $licenciaturas[$id]['grados'][$grado] = ['grado' => $grado, 'hombres' => 0, 'mujeres' => 0, 'total' => 0];
            }

            foreach (['hombres', 'mujeres', 'total'] as $campo) {
                $valor = (int) $fila->{$campo};
                $licenciaturas[$id]['grados'][$grado][$campo] += $valor;
                $licenciaturas[$id]['totales'][$campo] += $valor;
                $totales[$campo] += $valor;
            }
        }

        foreach ($licenciaturas as &$licenciatura) {
            if ($filtros['mostrar_grados_vacios']) {
                for ($grado = 1; $grado <= 3; $grado++) {
                    $licenciatura['grados'][$grado] ??= [
                        'grado' => $grado, 'hombres' => 0, 'mujeres' => 0, 'total' => 0,
                    ];
                }
            }
            ksort($licenciatura['grados']);
            $licenciatura['grados'] = array_values($licenciatura['grados']);
        }
        unset($licenciatura);

        return [
            'filtros' => $filtros,
            'fecha_corte' => $hoy,
            'licenciaturas' => array_values($licenciaturas),
            'generaciones' => $this->generacionesVigentes(),
            'totales' => $totales,
            // Las alertas comparten exactamente los filtros de los documentos y de la pantalla.
            'alertas_avance' => app(AcademicProgressAlertService::class)->evaluar($filtros),
        ];
    }
}
