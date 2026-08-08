<?php

namespace App\Services;

use App\Models\Dashboard;
use App\Models\Escuela;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListaProfesorChecklistService
{
    /**
     * @return array{
     *     ciclo_escolar:string,
     *     periodo_escolar:string,
     *     mes_id:int|null,
     *     periodo_rango:string,
     *     escuela:mixed
     * }
     */
    public function contextoAcademico(): array
    {
        $dashboard = Dashboard::query()->latest('id')->first();
        $periodo = mb_strtoupper(trim((string) ($dashboard?->periodo_escolar ?? '')), 'UTF-8');

        $mesId = null;
        if ($periodo !== '') {
            $mesId = DB::table('meses')
                ->whereRaw('UPPER(meses_corto) = ?', [$periodo])
                ->value('id');
        }

        return [
            'ciclo_escolar' => trim((string) ($dashboard?->ciclo_escolar ?? '')),
            'periodo_escolar' => $periodo,
            'mes_id' => $mesId ? (int) $mesId : null,
            'periodo_rango' => $this->rangoPeriodo($periodo),
            'escuela' => Escuela::query()->first(),
        ];
    }

    /**
     * @param  array{licenciatura_id?:int|string|null,modalidad_id?:int|string|null,generacion_id?:int|string|null}  $filtros
     * @return array<string, mixed>
     */
    public function construirReporte(array $filtros = []): array
    {
        $filtros = $this->normalizarFiltros($filtros);
        $contexto = $this->contextoAcademico();
        $filas = $this->filas($filtros);

        $profesores = $filas
            ->groupBy('profesor_id')
            ->map(function (Collection $registros) {
                $primero = $registros->first();

                return [
                    'id' => (int) $primero->profesor_id,
                    'nombre' => trim(collect([
                        $primero->apellido_paterno,
                        $primero->apellido_materno,
                        $primero->nombre,
                    ])->filter()->implode(' ')),
                    'registros' => $registros->values(),
                    'total_listas' => $registros->count(),
                    'total_documentos' => $registros->count() * 2,
                ];
            })
            ->values();

        $resumen = [
            'profesores' => $profesores->count(),
            'materias_grupos' => $filas->count(),
            'asistencias' => $filas->count(),
            'evaluaciones' => $filas->count(),
            'documentos' => $filas->count() * 2,
        ];

        return [
            'contexto' => $contexto,
            'filtros' => $filtros,
            'filtros_texto' => $this->filtrosTexto($filtros),
            'filas' => $filas,
            'profesores' => $profesores,
            'resumen' => $resumen,
            'fecha_emision' => now(),
        ];
    }

    /**
     * Resumen liviano para la tarjeta de la interfaz.
     *
     * @param  array<string, mixed>  $filtros
     * @return array{profesores:int,materias_grupos:int,asistencias:int,evaluaciones:int,documentos:int}
     */
    public function resumen(array $filtros = []): array
    {
        $filas = $this->filas($filtros);
        $total = $filas->count();

        return [
            'profesores' => $filas->pluck('profesor_id')->unique()->count(),
            'materias_grupos' => $total,
            'asistencias' => $total,
            'evaluaciones' => $total,
            'documentos' => $total * 2,
        ];
    }

    /**
     * Devuelve únicamente opciones que realmente existen dentro del ciclo/periodo
     * académico activo y que, además, tienen un horario ligado a un profesor.
     *
     * @return array{licenciaturas:Collection,modalidades:Collection,generaciones:Collection}
     */
    public function opcionesFiltros(): array
    {
        $filas = $this->filas([]);

        return [
            'licenciaturas' => $filas
                ->map(fn ($fila) => ['id' => (int) $fila->licenciatura_id, 'nombre' => $fila->licenciatura])
                ->unique('id')
                ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
                ->values(),
            'modalidades' => $filas
                ->map(fn ($fila) => ['id' => (int) $fila->modalidad_id, 'nombre' => $fila->modalidad])
                ->unique('id')
                ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
                ->values(),
            'generaciones' => $filas
                ->map(fn ($fila) => ['id' => (int) $fila->generacion_id, 'nombre' => $fila->generacion])
                ->unique('id')
                ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
                ->values(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function nombreArchivo(array $filtros, string $extension): string
    {
        $contexto = $this->contextoAcademico();
        $ciclo = $this->segmentoArchivo($contexto['ciclo_escolar'] ?: 'SIN-CICLO');
        $periodo = $this->segmentoArchivo($contexto['periodo_escolar'] ?: 'SIN-PERIODO');
        $fecha = now()->format('Y-m-d');

        return "CHECKLIST_LISTAS_PROFESORES_{$ciclo}_{$periodo}_{$fecha}.{$extension}";
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    private function filas(array $filtros): Collection
    {
        $filtros = $this->normalizarFiltros($filtros);

        return $this->consultaBase()
            ->when($filtros['licenciatura_id'], fn (Builder $query, int $id) => $query->where('l.id', $id))
            ->when($filtros['modalidad_id'], fn (Builder $query, int $id) => $query->where('mo.id', $id))
            ->when($filtros['generacion_id'], fn (Builder $query, int $id) => $query->where('g.id', $id))
            ->select([
                'p.id as profesor_id',
                'p.nombre',
                'p.apellido_paterno',
                'p.apellido_materno',
                'm.id as materia_id',
                'm.nombre as materia',
                'l.id as licenciatura_id',
                'l.nombre as licenciatura',
                'mo.id as modalidad_id',
                'mo.nombre as modalidad',
                'c.id as cuatrimestre_id',
                'c.cuatrimestre',
                'g.id as generacion_id',
                'g.generacion',
            ])
            ->distinct()
            ->orderBy('p.apellido_paterno')
            ->orderBy('p.apellido_materno')
            ->orderBy('p.nombre')
            ->orderBy('l.nombre')
            ->orderByRaw('CAST(c.cuatrimestre AS UNSIGNED)')
            ->orderBy('m.nombre')
            ->orderBy('g.generacion')
            ->get();
    }

    private function consultaBase(): Builder
    {
        // La fuente de verdad para este checklist es la tabla horarios: el
        // usuario pidió incluir a todos los profesores que actualmente tienen
        // materias ahí. El ciclo y periodo del Dashboard se usan como contexto
        // documental del reporte, sin descartar horarios por una configuración
        // del encabezado que pudiera estar pendiente de actualizarse.
        return DB::table('horarios as h')
            ->join('asignacion_materias as am', 'am.id', '=', 'h.asignacion_materia_id')
            ->join('profesores as p', 'p.id', '=', 'am.profesor_id')
            ->join('materias as m', 'm.id', '=', 'am.materia_id')
            ->join('licenciaturas as l', 'l.id', '=', 'am.licenciatura_id')
            ->join('modalidades as mo', 'mo.id', '=', 'am.modalidad_id')
            ->join('cuatrimestres as c', 'c.id', '=', 'am.cuatrimestre_id')
            ->join('generaciones as g', 'g.id', '=', 'h.generacion_id')
            ->whereNotNull('am.profesor_id')
            ->whereNotNull('h.generacion_id');
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{licenciatura_id:int|null,modalidad_id:int|null,generacion_id:int|null}
     */
    private function normalizarFiltros(array $filtros): array
    {
        return [
            'licenciatura_id' => $this->enteroONull($filtros['licenciatura_id'] ?? null),
            'modalidad_id' => $this->enteroONull($filtros['modalidad_id'] ?? null),
            'generacion_id' => $this->enteroONull($filtros['generacion_id'] ?? null),
        ];
    }

    /**
     * @param  array{licenciatura_id:int|null,modalidad_id:int|null,generacion_id:int|null}  $filtros
     */
    private function filtrosTexto(array $filtros): array
    {
        return [
            'licenciatura' => $filtros['licenciatura_id']
                ? DB::table('licenciaturas')->where('id', $filtros['licenciatura_id'])->value('nombre')
                : 'Todas',
            'modalidad' => $filtros['modalidad_id']
                ? DB::table('modalidades')->where('id', $filtros['modalidad_id'])->value('nombre')
                : 'Todas',
            'generacion' => $filtros['generacion_id']
                ? DB::table('generaciones')->where('id', $filtros['generacion_id'])->value('generacion')
                : 'Todas',
        ];
    }

    private function enteroONull(mixed $valor): ?int
    {
        if ($valor === null || $valor === '' || ! is_numeric($valor)) {
            return null;
        }

        $entero = (int) $valor;

        return $entero > 0 ? $entero : null;
    }

    private function rangoPeriodo(string $periodo): string
    {
        return match ($periodo) {
            'SEP/DIC' => '9-12',
            'ENE/ABR' => '1-4',
            'MAY/AGO' => '5-8',
            default => '',
        };
    }

    private function segmentoArchivo(string $texto): string
    {
        $texto = preg_replace('/[^A-Za-z0-9_-]+/', '_', $texto) ?: 'SIN_DATO';

        return trim($texto, '_');
    }
}
