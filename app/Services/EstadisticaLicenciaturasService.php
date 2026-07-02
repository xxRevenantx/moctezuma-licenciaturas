<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EstadisticaLicenciaturasService
{
    /**
     * Normaliza los filtros recibidos desde Livewire o desde la URL.
     */
    public function normalizarFiltros(array $filtros): array
    {
        return [
            'ciclo_escolar' => $this->textoONull($filtros['ciclo_escolar'] ?? null),
            'licenciatura_id' => $this->enteroONull($filtros['licenciatura_id'] ?? null),
            'modalidad_id' => $this->enteroONull($filtros['modalidad_id'] ?? null),
            'generacion_id' => $this->enteroONull($filtros['generacion_id'] ?? null),
            'cuatrimestre_id' => $this->enteroONull($filtros['cuatrimestre_id'] ?? null),
            'separar_modalidades' => $this->booleano($filtros['separar_modalidades'] ?? true),
            'detalle_cuatrimestres' => $this->booleano($filtros['detalle_cuatrimestres'] ?? true),
        ];
    }

    /**
     * Obtiene el reporte agregado de todos los alumnos, sin excluir bajas ni egresados.
     *
     * Reglas de estado, sin traslapes:
     * 1. Baja: status = false o fecha_baja informada.
     * 2. Egresado: no es baja y egresado = true, su generación está inactiva o concluyó el 9.º cuatrimestre.
     * 3. Activo: no cumple ninguna de las condiciones anteriores.
     */
    public function generar(array $filtros = []): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        $periodos = DB::table('periodos')
            ->select([
                'generacion_id',
                'cuatrimestre_id',
                DB::raw('MAX(ciclo_escolar) AS ciclo_escolar'),
                DB::raw('MAX(termino_periodo) AS termino_periodo'),
            ])
            ->groupBy('generacion_id', 'cuatrimestre_id');

        $esBaja = "(i.status = 'false' OR i.fecha_baja IS NOT NULL)";
        $condicionEgreso = "(i.egresado = 'true' OR g.activa = 'false' OR (c.cuatrimestre = '9' AND p.termino_periodo IS NOT NULL AND p.termino_periodo < CURDATE()))";
        $esEgresado = "(NOT {$esBaja} AND {$condicionEgreso})";
        $esActivo = "(NOT {$esBaja} AND NOT {$condicionEgreso})";

        $query = DB::table('inscripciones as i')
            ->join('licenciaturas as l', 'l.id', '=', 'i.licenciatura_id')
            ->join('generaciones as g', 'g.id', '=', 'i.generacion_id')
            ->join('cuatrimestres as c', 'c.id', '=', 'i.cuatrimestre_id')
            ->join('modalidades as m', 'm.id', '=', 'i.modalidad_id')
            ->leftJoinSub($periodos, 'p', function ($join) {
                $join->on('p.generacion_id', '=', 'i.generacion_id')
                    ->on('p.cuatrimestre_id', '=', 'i.cuatrimestre_id');
            })
            ->when($filtros['ciclo_escolar'], fn ($q, $valor) => $q->where('p.ciclo_escolar', $valor))
            ->when($filtros['licenciatura_id'], fn ($q, $valor) => $q->where('i.licenciatura_id', $valor))
            ->when($filtros['modalidad_id'], fn ($q, $valor) => $q->where('i.modalidad_id', $valor))
            ->when($filtros['generacion_id'], fn ($q, $valor) => $q->where('i.generacion_id', $valor))
            ->when($filtros['cuatrimestre_id'], fn ($q, $valor) => $q->where('i.cuatrimestre_id', $valor));

        $selects = [
            DB::raw("COALESCE(p.ciclo_escolar, CONCAT('SIN CICLO · ', g.generacion)) AS ciclo_escolar"),
            'l.id as licenciatura_id',
            'l.nombre as licenciatura',
            'l.RVOE as rvoe',
            'g.id as generacion_id',
            'g.generacion',
            DB::raw("SUM(CASE WHEN {$esActivo} AND i.sexo = 'H' THEN 1 ELSE 0 END) AS activos_hombres"),
            DB::raw("SUM(CASE WHEN {$esActivo} AND i.sexo = 'M' THEN 1 ELSE 0 END) AS activos_mujeres"),
            DB::raw("SUM(CASE WHEN {$esActivo} THEN 1 ELSE 0 END) AS activos_total"),
            DB::raw("SUM(CASE WHEN {$esBaja} AND i.sexo = 'H' THEN 1 ELSE 0 END) AS bajas_hombres"),
            DB::raw("SUM(CASE WHEN {$esBaja} AND i.sexo = 'M' THEN 1 ELSE 0 END) AS bajas_mujeres"),
            DB::raw("SUM(CASE WHEN {$esBaja} THEN 1 ELSE 0 END) AS bajas_total"),
            DB::raw("SUM(CASE WHEN {$esEgresado} AND i.sexo = 'H' THEN 1 ELSE 0 END) AS egresados_hombres"),
            DB::raw("SUM(CASE WHEN {$esEgresado} AND i.sexo = 'M' THEN 1 ELSE 0 END) AS egresados_mujeres"),
            DB::raw("SUM(CASE WHEN {$esEgresado} THEN 1 ELSE 0 END) AS egresados_total"),
            DB::raw("SUM(CASE WHEN i.sexo = 'H' THEN 1 ELSE 0 END) AS hombres_total"),
            DB::raw("SUM(CASE WHEN i.sexo = 'M' THEN 1 ELSE 0 END) AS mujeres_total"),
            DB::raw('COUNT(*) AS total_general'),
        ];

        $groupBy = [
            'p.ciclo_escolar',
            'g.generacion',
            'l.id',
            'l.nombre',
            'l.RVOE',
            'g.id',
        ];

        if ($filtros['separar_modalidades']) {
            $selects[] = 'm.id as modalidad_id';
            $selects[] = 'm.nombre as modalidad';
            $groupBy[] = 'm.id';
            $groupBy[] = 'm.nombre';
        } else {
            $selects[] = DB::raw('NULL AS modalidad_id');
            $selects[] = DB::raw("'TODAS' AS modalidad");
        }

        if ($filtros['detalle_cuatrimestres']) {
            $selects[] = 'c.id as cuatrimestre_id';
            $selects[] = 'c.nombre_cuatrimestre as cuatrimestre';
            $groupBy[] = 'c.id';
            $groupBy[] = 'c.nombre_cuatrimestre';
        } else {
            $selects[] = DB::raw('NULL AS cuatrimestre_id');
            $selects[] = DB::raw("'RESUMEN' AS cuatrimestre");
        }

        $filas = $query
            ->select($selects)
            ->groupBy($groupBy)
            ->orderByRaw("CASE WHEN p.ciclo_escolar IS NULL THEN 1 ELSE 0 END")
            ->orderBy('p.ciclo_escolar')
            ->orderBy('l.nombre')
            ->when($filtros['separar_modalidades'], fn ($q) => $q->orderBy('m.nombre'))
            ->orderBy('g.generacion')
            ->when($filtros['detalle_cuatrimestres'], fn ($q) => $q->orderBy('c.id'))
            ->get()
            ->map(fn ($fila) => $this->normalizarFila($fila));

        $secciones = $filas
            ->groupBy('ciclo_escolar')
            ->map(function (Collection $filasCiclo, string $ciclo) {
                return [
                    'ciclo_escolar' => $ciclo,
                    'filas' => $filasCiclo->values(),
                    'totales' => $this->sumar($filasCiclo),
                ];
            })
            ->values();

        return [
            'filtros' => $filtros,
            'filas' => $filas->values(),
            'secciones' => $secciones,
            'totales' => $this->sumar($filas),
        ];
    }

    private function normalizarFila(object $fila): array
    {
        return [
            'ciclo_escolar' => (string) $fila->ciclo_escolar,
            'licenciatura_id' => (int) $fila->licenciatura_id,
            'licenciatura' => (string) $fila->licenciatura,
            'rvoe' => $fila->rvoe ?: 'SIN RVOE',
            'modalidad_id' => $fila->modalidad_id ? (int) $fila->modalidad_id : null,
            'modalidad' => (string) $fila->modalidad,
            'generacion_id' => (int) $fila->generacion_id,
            'generacion' => (string) $fila->generacion,
            'cuatrimestre_id' => $fila->cuatrimestre_id ? (int) $fila->cuatrimestre_id : null,
            'cuatrimestre' => (string) $fila->cuatrimestre,
            'activos_hombres' => (int) $fila->activos_hombres,
            'activos_mujeres' => (int) $fila->activos_mujeres,
            'activos_total' => (int) $fila->activos_total,
            'bajas_hombres' => (int) $fila->bajas_hombres,
            'bajas_mujeres' => (int) $fila->bajas_mujeres,
            'bajas_total' => (int) $fila->bajas_total,
            'egresados_hombres' => (int) $fila->egresados_hombres,
            'egresados_mujeres' => (int) $fila->egresados_mujeres,
            'egresados_total' => (int) $fila->egresados_total,
            'hombres_total' => (int) $fila->hombres_total,
            'mujeres_total' => (int) $fila->mujeres_total,
            'total_general' => (int) $fila->total_general,
        ];
    }

    private function sumar(Collection $filas): array
    {
        $campos = [
            'activos_hombres',
            'activos_mujeres',
            'activos_total',
            'bajas_hombres',
            'bajas_mujeres',
            'bajas_total',
            'egresados_hombres',
            'egresados_mujeres',
            'egresados_total',
            'hombres_total',
            'mujeres_total',
            'total_general',
        ];

        return collect($campos)
            ->mapWithKeys(fn (string $campo) => [$campo => (int) $filas->sum($campo)])
            ->all();
    }

    private function enteroONull(mixed $valor): ?int
    {
        return filled($valor) ? (int) $valor : null;
    }

    private function textoONull(mixed $valor): ?string
    {
        $valor = is_string($valor) ? trim($valor) : $valor;

        return filled($valor) ? (string) $valor : null;
    }

    private function booleano(mixed $valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }

        return filter_var($valor, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }
}
