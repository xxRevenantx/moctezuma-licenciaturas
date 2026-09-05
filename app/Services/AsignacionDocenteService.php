<?php

namespace App\Services;

use App\Models\AsignacionMateria;
use App\Models\Calificacion;
use App\Models\CalificacionDocenteCaptura;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Profesor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AsignacionDocenteService
{
    public function __construct(private readonly HorarioTraslapeService $traslapes)
    {
    }

    /**
     * @param array<int,array{materia_id:int,modalidad_id:int}> $objetivos
     * @return array<string,mixed>
     */
    public function preparar(array $objetivos, ?int $profesorId): array
    {
        $objetivos = $this->normalizarObjetivos($objetivos);

        if ($objetivos->isEmpty()) {
            throw new RuntimeException('No hay materias válidas para actualizar.');
        }

        $profesorDestino = null;
        if ($profesorId !== null) {
            $profesorDestino = Profesor::query()->find($profesorId);

            if (! $profesorDestino) {
                throw new RuntimeException('El profesor seleccionado ya no existe.');
            }
        }

        $materias = Materia::query()
            ->whereIn('id', $objetivos->pluck('materia_id')->unique()->all())
            ->get(['id', 'nombre', 'clave', 'licenciatura_id', 'cuatrimestre_id'])
            ->keyBy('id');

        if ($materias->count() !== $objetivos->pluck('materia_id')->unique()->count()) {
            throw new RuntimeException('Una o más materias ya no existen.');
        }

        $asignaciones = AsignacionMateria::query()
            ->whereIn('materia_id', $objetivos->pluck('materia_id')->unique()->all())
            ->whereIn('modalidad_id', $objetivos->pluck('modalidad_id')->unique()->all())
            ->get(['id', 'materia_id', 'licenciatura_id', 'modalidad_id', 'cuatrimestre_id', 'profesor_id'])
            ->groupBy(fn (AsignacionMateria $a) => $this->clave((int) $a->materia_id, (int) $a->modalidad_id));

        $cambios = collect();

        foreach ($objetivos as $objetivo) {
            $materia = $materias->get($objetivo['materia_id']);
            $clave = $this->clave($objetivo['materia_id'], $objetivo['modalidad_id']);
            /** @var Collection<int,AsignacionMateria> $grupo */
            $grupo = $asignaciones->get($clave, collect());

            $grupoCorrecto = $grupo->filter(fn (AsignacionMateria $a) =>
                (int) $a->licenciatura_id === (int) $materia->licenciatura_id
                && (int) $a->cuatrimestre_id === (int) $materia->cuatrimestre_id
            )->values();

            if ($grupoCorrecto->count() > 1) {
                throw new RuntimeException("La materia {$materia->nombre} tiene asignaciones duplicadas para la modalidad seleccionada.");
            }

            if ($grupo->count() !== $grupoCorrecto->count()) {
                throw new RuntimeException("La materia {$materia->nombre} tiene una asignación inconsistente con su licenciatura o cuatrimestre.");
            }

            $asignacion = $grupoCorrecto->first();
            $actual = $asignacion?->profesor_id ? (int) $asignacion->profesor_id : null;

            if ($actual === $profesorId) {
                continue;
            }

            $cambios->push([
                'materia_id' => (int) $materia->id,
                'modalidad_id' => (int) $objetivo['modalidad_id'],
                'licenciatura_id' => (int) $materia->licenciatura_id,
                'cuatrimestre_id' => (int) $materia->cuatrimestre_id,
                'asignacion_id' => $asignacion?->id ? (int) $asignacion->id : null,
            ]);
        }

        if ($cambios->isEmpty()) {
            return [
                'sin_cambios' => true,
                'objetivos' => [],
                'conflictos' => [],
                'impacto' => $this->impacto([]),
                'requiere_confirmacion' => false,
            ];
        }

        if ($profesorDestino && $profesorDestino->status !== 'true') {
            throw new RuntimeException('No se permiten nuevas asignaciones a profesores inactivos.');
        }

        $asignacionIds = $cambios->pluck('asignacion_id')->filter()->unique()->values()->all();

        if ($profesorId === null && ! empty($asignacionIds)) {
            $capturas = CalificacionDocenteCaptura::query()
                ->whereIn('asignacion_materia_id', $asignacionIds)
                ->count();

            if ($capturas > 0) {
                throw new RuntimeException('No puedes dejar sin profesor una materia que ya tiene capturas en Calificaciones por docente. Reasígnala directamente a otro profesor.');
            }
        }

        $conflictos = [];

        if ($profesorId !== null) {
            foreach ($cambios->groupBy('modalidad_id') as $modalidadId => $grupo) {
                $idsModalidad = $grupo->pluck('asignacion_id')->filter()->unique()->values()->all();

                if (empty($idsModalidad)) {
                    continue;
                }

                $conflictos = array_merge(
                    $conflictos,
                    $this->traslapes->conflictosParaCambioProfesor($profesorId, (int) $modalidadId, $idsModalidad)
                );
            }
        }

        $impacto = $this->impacto($asignacionIds);

        return [
            'sin_cambios' => false,
            'objetivos' => $cambios->map(fn (array $c) => [
                'materia_id' => $c['materia_id'],
                'modalidad_id' => $c['modalidad_id'],
            ])->values()->all(),
            'conflictos' => collect($conflictos)->unique(fn (array $c) => implode('|', [
                $c['horario_origen_id'] ?? '',
                $c['horario_conflicto_id'] ?? '',
                $c['tipo'] ?? '',
            ]))->values()->all(),
            'impacto' => $impacto,
            'requiere_confirmacion' => ! empty($conflictos)
                || $impacto['calificaciones'] > 0
                || $impacto['capturas'] > 0,
        ];
    }

    /**
     * @param array<int,array{materia_id:int,modalidad_id:int}> $objetivos
     * @return array{cambios:int,auditoria:array<int,array<string,mixed>>}
     */
    public function ejecutar(array $objetivos, ?int $profesorId, ?int $usuarioId = null): array
    {
        $objetivos = $this->normalizarObjetivos($objetivos);

        if ($objetivos->isEmpty()) {
            throw new RuntimeException('No hay materias válidas para actualizar.');
        }

        if ($profesorId !== null) {
            $profesor = Profesor::query()->find($profesorId);
            if (! $profesor || $profesor->status !== 'true') {
                throw new RuntimeException('El profesor seleccionado no está disponible para nuevas asignaciones.');
            }
        }

        $materias = Materia::query()
            ->whereIn('id', $objetivos->pluck('materia_id')->unique()->all())
            ->get(['id', 'nombre', 'clave', 'licenciatura_id', 'cuatrimestre_id'])
            ->keyBy('id');

        if ($materias->count() !== $objetivos->pluck('materia_id')->unique()->count()) {
            throw new RuntimeException('Una o más materias dejaron de estar disponibles durante la operación.');
        }

        if ($profesorId === null) {
            $idsExistentes = collect();
            foreach ($objetivos as $objetivo) {
                $materia = $materias->get($objetivo['materia_id']);
                $idsExistentes = $idsExistentes->merge(
                    AsignacionMateria::query()
                        ->where('materia_id', $materia->id)
                        ->where('licenciatura_id', $materia->licenciatura_id)
                        ->where('modalidad_id', $objetivo['modalidad_id'])
                        ->where('cuatrimestre_id', $materia->cuatrimestre_id)
                        ->pluck('id')
                );
            }

            $idsExistentes = $idsExistentes->map(fn ($id) => (int) $id)->unique()->values();
            if ($idsExistentes->isNotEmpty() && CalificacionDocenteCaptura::query()->whereIn('asignacion_materia_id', $idsExistentes->all())->exists()) {
                throw new RuntimeException('No puedes dejar sin profesor una materia que ya tiene capturas en Calificaciones por docente. Reasígnala directamente a otro profesor.');
            }
        }

        $resultado = DB::transaction(function () use ($objetivos, $materias, $profesorId, $usuarioId) {
            $cambios = 0;
            $auditoria = [];

            foreach ($objetivos as $objetivo) {
                $materia = $materias->get($objetivo['materia_id']);
                $criterios = [
                    'materia_id' => (int) $materia->id,
                    'licenciatura_id' => (int) $materia->licenciatura_id,
                    'modalidad_id' => (int) $objetivo['modalidad_id'],
                    'cuatrimestre_id' => (int) $materia->cuatrimestre_id,
                ];

                $coincidentes = AsignacionMateria::query()
                    ->where($criterios)
                    ->lockForUpdate()
                    ->get();

                if ($coincidentes->count() > 1) {
                    throw new RuntimeException("La materia {$materia->nombre} tiene asignaciones duplicadas.");
                }

                $asignacion = $coincidentes->first();

                if (! $asignacion && $profesorId === null) {
                    continue;
                }

                if (! $asignacion) {
                    $asignacion = new AsignacionMateria($criterios);
                }

                $profesorAnteriorId = $asignacion->profesor_id ? (int) $asignacion->profesor_id : null;

                if ($profesorAnteriorId === $profesorId) {
                    continue;
                }

                // No se elimina la asignación: horarios/calificaciones dependen de su id.
                $asignacion->profesor_id = $profesorId;
                $asignacion->save();
                $cambios++;

                $auditoria[] = [
                    'user_id' => $usuarioId,
                    'asignacion_materia_id' => (int) $asignacion->id,
                    'materia_id' => (int) $materia->id,
                    'licenciatura_id' => (int) $materia->licenciatura_id,
                    'modalidad_id' => (int) $objetivo['modalidad_id'],
                    'cuatrimestre_id' => (int) $materia->cuatrimestre_id,
                    'profesor_anterior_id' => $profesorAnteriorId,
                    'profesor_nuevo_id' => $profesorId,
                ];
            }

            return compact('cambios', 'auditoria');
        });

        foreach ($resultado['auditoria'] as $registro) {
            Log::info('Cambio de profesor en asignación de materia', $registro);
        }

        return $resultado;
    }

    /** @param array<int> $asignacionIds */
    public function impacto(array $asignacionIds): array
    {
        $asignacionIds = collect($asignacionIds)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();

        if (empty($asignacionIds)) {
            return ['horarios' => 0, 'calificaciones' => 0, 'capturas' => 0, 'entregadas' => 0, 'validadas' => 0];
        }

        return [
            'horarios' => Horario::query()->whereIn('asignacion_materia_id', $asignacionIds)->count(),
            'calificaciones' => Calificacion::query()->whereIn('asignacion_materia_id', $asignacionIds)->count(),
            'capturas' => CalificacionDocenteCaptura::query()->whereIn('asignacion_materia_id', $asignacionIds)->count(),
            'entregadas' => CalificacionDocenteCaptura::query()->whereIn('asignacion_materia_id', $asignacionIds)->where('estado', 'entregada')->count(),
            'validadas' => CalificacionDocenteCaptura::query()->whereIn('asignacion_materia_id', $asignacionIds)->where('estado', 'validada')->count(),
        ];
    }

    /** @return Collection<int,array{materia_id:int,modalidad_id:int}> */
    private function normalizarObjetivos(array $objetivos): Collection
    {
        return collect($objetivos)
            ->map(fn ($o) => [
                'materia_id' => (int) ($o['materia_id'] ?? 0),
                'modalidad_id' => (int) ($o['modalidad_id'] ?? 0),
            ])
            ->filter(fn (array $o) => $o['materia_id'] > 0 && $o['modalidad_id'] > 0)
            ->unique(fn (array $o) => $this->clave($o['materia_id'], $o['modalidad_id']))
            ->values();
    }

    private function clave(int $materiaId, int $modalidadId): string
    {
        return $materiaId . ':' . $modalidadId;
    }
}
