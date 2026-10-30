<?php

namespace App\Services;

use App\Models\Horario;
use App\Models\Periodo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Reporte de docentes con horario REAL en el ciclo y periodo elegidos. */
class PlantillaDocenteLicenciaturaService
{
    private const CATEGORIAS = ['licenciaturas', 'modalidades', 'generaciones', 'cuatrimestres'];

    public function ciclos(): Collection
    {
        return Periodo::query()->whereNotNull('ciclo_escolar')->distinct()
            ->orderByDesc('ciclo_escolar')->pluck('ciclo_escolar');
    }

    public function periodos(string $ciclo): Collection
    {
        return Periodo::query()->where('ciclo_escolar', $ciclo)
            ->with('mes:id,meses_corto')->get()
            ->pluck('mes.meses_corto')->filter()->unique()->values();
    }

    private function base(string $ciclo, string $periodo): Builder
    {
        $query = Horario::query()
            ->whereHas('asignacionMateria.profesor')
            ->with([
                'licenciatura:id,nombre,nombre_corto',
                'modalidad:id,nombre',
                'generacion:id,generacion',
                'cuatrimestre:id,cuatrimestre,nombre_cuatrimestre',
                'asignacionMateria.profesor:id,nombre,apellido_paterno,apellido_materno',
                'asignacionMateria.materia:id,nombre',
            ]);

        // Importante: filtra por el PAR generación/cuatrimestre del periodo, no solo por año.
        return app(HorarioGeneralService::class)->aplicarCiclo($query, $ciclo, $periodo);
    }

    public function opciones(string $ciclo, string $periodo): array
    {
        $horarios = $this->base($ciclo, $periodo)->get();
        return [
            'licenciaturas' => $horarios->filter(fn ($h) => $h->licenciatura)
                ->map(fn ($h) => ['id' => (int) $h->licenciatura_id, 'nombre' => $h->licenciatura->nombre])
                ->unique('id')->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values()->all(),
            'modalidades' => $horarios->filter(fn ($h) => $h->modalidad)
                ->map(fn ($h) => ['id' => (int) $h->modalidad_id, 'nombre' => $h->modalidad->nombre])
                ->unique('id')->sortBy('nombre')->values()->all(),
            'generaciones' => $horarios->filter(fn ($h) => $h->generacion)
                ->map(fn ($h) => ['id' => (int) $h->generacion_id, 'nombre' => $h->generacion->generacion])
                ->unique('id')->sortByDesc('nombre')->values()->all(),
            'cuatrimestres' => $horarios->filter(fn ($h) => $h->cuatrimestre)
                ->map(fn ($h) => ['id' => (int) $h->cuatrimestre_id, 'nombre' => $h->cuatrimestre->cuatrimestre . 'º CUATRIMESTRE'])
                ->unique('id')->sortBy('id')->values()->all(),
        ];
    }

    /**
     * Un filtro null = todos; array vacío = ninguno; array de ID = selección explícita.
     * @param array{ciclo_escolar?:string,periodo_escolar?:string,licenciaturas?:?array,modalidades?:?array,generaciones?:?array,cuatrimestres?:?array,materias?:bool,carga_horaria?:bool,mostrar_generaciones?:bool} $filtros
     */
    public function generar(array $filtros): array
    {
        $ciclo = (string) ($filtros['ciclo_escolar'] ?? '2026-2027');
        $periodo = (string) ($filtros['periodo_escolar'] ?? 'SEP/DIC');
        $query = $this->base($ciclo, $periodo);

        $columnas = [
            'licenciaturas' => 'licenciatura_id',
            'modalidades' => 'modalidad_id',
            'generaciones' => 'generacion_id',
            'cuatrimestres' => 'cuatrimestre_id',
        ];
        foreach ($columnas as $nombre => $columna) {
            if (array_key_exists($nombre, $filtros) && $filtros[$nombre] !== null) {
                $ids = array_values(array_unique(array_filter(array_map('intval', (array) $filtros[$nombre]), fn ($id) => $id > 0)));
                $ids ? $query->whereIn($columna, $ids) : $query->whereRaw('1 = 0');
            }
        }

        $horarios = $query->get()->filter(fn ($h) => $h->licenciatura && $h->asignacionMateria?->profesor);
        $grupos = $this->agruparHorarios($horarios);
        $profesoresUnicos = $horarios->map(fn ($h) => (int) $h->asignacionMateria->profesor_id)->unique()->count();
        $minutos = array_sum(array_column($grupos, 'minutos'));

        return [
            'ciclo' => $ciclo,
            'periodo' => $periodo,
            'fecha' => now()->format('d/m/Y'),
            'fuente' => 'Docentes con horario programado',
            'mostrar_materias' => (bool) ($filtros['materias'] ?? true),
            'mostrar_horas' => (bool) ($filtros['carga_horaria'] ?? true),
            'mostrar_generaciones' => (bool) ($filtros['mostrar_generaciones'] ?? true),
            'grupos' => $grupos,
            'totales' => [
                'licenciaturas' => count($grupos),
                'profesores_unicos' => $profesoresUnicos,
                'participaciones' => array_sum(array_column($grupos, 'total_docentes')),
                'bloques' => $horarios->count(),
                'minutos' => $minutos,
                'horas' => app(HorarioGeneralService::class)->formatoHoras($minutos),
            ],
        ];
    }

    /** @internal Se mantiene separado para probar agrupación, horas y duplicados sin BD. */
    public function agruparHorarios(Collection $horarios): array
    {
        $servicio = app(HorarioGeneralService::class);
        $grupos = [];
        foreach ($horarios->groupBy('licenciatura_id') as $licId => $registros) {
            $primero = $registros->first();
            $docentes = [];
            foreach ($registros->groupBy(fn ($h) => $h->asignacionMateria->profesor->id) as $id => $bloques) {
                $profesor = $bloques->first()->asignacionMateria->profesor;
                $materias = $bloques->filter(fn ($h) => $h->asignacionMateria?->materia)
                    ->unique(fn ($h) => $h->asignacionMateria->materia->id)
                    ->map(fn ($h) => $h->asignacionMateria->materia->nombre)->sort(SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
                $cuatrimestres = $bloques->map(fn ($h) => (int) ($h->cuatrimestre?->cuatrimestre ?? $h->cuatrimestre_id))
                    ->unique()->sort()->map(fn ($v) => $v.'º')->values()->all();
                $generaciones = $bloques->pluck('generacion.generacion')->filter()->unique()->sort()->values()->all();
                $modalidades = $bloques->pluck('modalidad.nombre')->filter()->unique()->sort()->values()->all();
                $minutos = (int) $bloques->sum(fn ($h) => $servicio->minutosRango((string) $h->hora));
                $docentes[] = [
                    'id' => (int) $id,
                    'nombre' => trim(implode(' ', array_filter([
                        $profesor->apellido_paterno, $profesor->apellido_materno, $profesor->nombre,
                    ], fn ($v) => trim((string) $v) !== ''))),
                    'materias' => $materias,
                    'cuatrimestres' => $cuatrimestres,
                    'modalidades' => $modalidades,
                    'generaciones' => $generaciones,
                    'bloques' => $bloques->count(),
                    'minutos' => $minutos,
                    'horas' => $servicio->formatoHoras($minutos),
                ];
            }
            usort($docentes, fn ($a, $b) => strnatcasecmp($a['nombre'], $b['nombre']));
            $minutos = array_sum(array_column($docentes, 'minutos'));
            $grupos[] = [
                'licenciatura_id' => (int) $licId,
                'licenciatura' => $primero->licenciatura->nombre,
                'docentes' => $docentes,
                'total_docentes' => count($docentes),
                'bloques' => $registros->count(),
                'minutos' => $minutos,
                'horas' => $servicio->formatoHoras($minutos),
            ];
        }
        usort($grupos, fn ($a, $b) => strnatcasecmp($a['licenciatura'], $b['licenciatura']));
        return $grupos;
    }
}
