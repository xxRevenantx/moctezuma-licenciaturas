<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Models\AsignacionMateria;
use App\Models\Calificacion;
use App\Models\CalificacionDocenteCaptura;
use App\Models\Cuatrimestre;
use App\Models\Horario;
use App\Models\Licenciatura;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\Profesor;
use App\Services\HorarioTraslapeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;
use Throwable;

class AsignarMateria extends Component
{
    use WithPagination;

    public $search = '';
    public $modalidad;
    public $licenciatura;
    public $submodulo;
    public $profesores;
    public $cuatrimestres;

    public $profesor_seleccionado = [];

    // Filtros
    public $filtrar_cuatrimestre = '';
    public $filtrar_asignacion = '';
    public $filtrar_profesor = '';
    public $filtrar_calificable = '';
    public $por_pagina = 20;

    // Asignación masiva
    public array $materias_seleccionadas = [];
    public $profesor_masivo = '';

    // Selector de profesor
    public $selector_modo = null; // individual | masiva
    public $selector_materia_id = null;

    // Confirmación preventiva
    public array $operacion_pendiente = [];
    public array $conflictos_pendientes = [];
    public array $impacto_pendiente = [];

    public function mount($licenciatura, $modalidad, $submodulo)
    {
        $this->licenciatura = Licenciatura::where('slug', $licenciatura)->firstOrFail();
        $this->modalidad = Modalidad::where('slug', $modalidad)->firstOrFail();
        $this->submodulo = $submodulo;

        $this->profesores = Profesor::query()
            ->orderByRaw("CASE WHEN status = 'true' THEN 0 ELSE 1 END")
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
                'apellido_paterno',
                'apellido_materno',
                'perfil',
                'color',
                'status',
            ]);

        $this->cuatrimestres = Cuatrimestre::query()
            ->whereHas('materias', function ($query) {
                $query->where('licenciatura_id', $this->licenciatura->id);
            })
            ->orderBy('cuatrimestre')
            ->get(['id', 'cuatrimestre', 'nombre_cuatrimestre']);
    }

    public function getMateriasProperty()
    {
        $licenciaturaId = $this->licenciatura->id;
        $modalidadId = $this->modalidad->id;

        $query = Materia::query()
            ->select([
                'id',
                'nombre',
                'clave',
                'cuatrimestre_id',
                'licenciatura_id',
                'orden',
                'calificable',
            ])
            ->with([
                'cuatrimestre:id,cuatrimestre,nombre_cuatrimestre',
                'asignacionMaterias' => function ($asignacionQuery) use ($licenciaturaId, $modalidadId) {
                    $asignacionQuery
                        ->select([
                            'id',
                            'materia_id',
                            'licenciatura_id',
                            'modalidad_id',
                            'cuatrimestre_id',
                            'profesor_id',
                        ])
                        ->where('licenciatura_id', $licenciaturaId)
                        ->where('modalidad_id', $modalidadId)
                        ->with('profesor:id,nombre,apellido_paterno,apellido_materno,color,status');
                },
            ])
            ->where('licenciatura_id', $licenciaturaId);

        $termino = trim((string) $this->search);

        if ($termino !== '') {
            $query->where(function ($searchQuery) use ($termino) {
                $searchQuery
                    ->where('nombre', 'like', '%' . $termino . '%')
                    ->orWhere('clave', 'like', '%' . $termino . '%');
            });
        }

        if ($this->filtrar_cuatrimestre !== '') {
            $query->where('cuatrimestre_id', $this->filtrar_cuatrimestre);
        }

        if (in_array($this->filtrar_calificable, ['true', 'false'], true)) {
            $query->where('calificable', $this->filtrar_calificable);
        }

        if ($this->filtrar_profesor !== '') {
            $profesorId = (int) $this->filtrar_profesor;

            $query->whereHas('asignacionMaterias', function ($asignacionQuery) use ($licenciaturaId, $modalidadId, $profesorId) {
                $asignacionQuery
                    ->where('licenciatura_id', $licenciaturaId)
                    ->where('modalidad_id', $modalidadId)
                    ->where('profesor_id', $profesorId);
            });
        }

        if ($this->filtrar_asignacion === 'asignadas') {
            $query->whereHas('asignacionMaterias', function ($asignacionQuery) use ($licenciaturaId, $modalidadId) {
                $asignacionQuery
                    ->where('licenciatura_id', $licenciaturaId)
                    ->where('modalidad_id', $modalidadId)
                    ->whereNotNull('profesor_id');
            });
        } elseif ($this->filtrar_asignacion === 'sin_asignar') {
            $query->whereDoesntHave('asignacionMaterias', function ($asignacionQuery) use ($licenciaturaId, $modalidadId) {
                $asignacionQuery
                    ->where('licenciatura_id', $licenciaturaId)
                    ->where('modalidad_id', $modalidadId)
                    ->whereNotNull('profesor_id');
            });
        }

        $porPagina = in_array((int) $this->por_pagina, [10, 20, 50, 100], true)
            ? (int) $this->por_pagina
            : 20;

        return $query
            ->orderBy('cuatrimestre_id')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate($porPagina);
    }

    public function getResumenProperty(): array
    {
        $totalMaterias = Materia::query()
            ->where('licenciatura_id', $this->licenciatura->id)
            ->count();

        $asignadas = AsignacionMateria::query()
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->whereNotNull('profesor_id')
            ->distinct()
            ->count('materia_id');

        $docentesAsignados = AsignacionMateria::query()
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->whereNotNull('profesor_id')
            ->distinct()
            ->count('profesor_id');

        return [
            'total' => $totalMaterias,
            'asignadas' => min($asignadas, $totalMaterias),
            'sin_asignar' => max($totalMaterias - $asignadas, 0),
            'docentes' => $docentesAsignados,
        ];
    }

    public function getProgresoCuatrimestresProperty(): array
    {
        $totales = Materia::query()
            ->where('licenciatura_id', $this->licenciatura->id)
            ->selectRaw('cuatrimestre_id, COUNT(*) as total')
            ->groupBy('cuatrimestre_id')
            ->pluck('total', 'cuatrimestre_id');

        $asignadas = AsignacionMateria::query()
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->whereNotNull('profesor_id')
            ->selectRaw('cuatrimestre_id, COUNT(DISTINCT materia_id) as total')
            ->groupBy('cuatrimestre_id')
            ->pluck('total', 'cuatrimestre_id');

        return $this->cuatrimestres
            ->map(function ($cuatrimestre) use ($totales, $asignadas) {
                $total = (int) ($totales[$cuatrimestre->id] ?? 0);
                $asignadasCuatrimestre = min((int) ($asignadas[$cuatrimestre->id] ?? 0), $total);

                return [
                    'id' => (int) $cuatrimestre->id,
                    'nombre' => $cuatrimestre->nombre_cuatrimestre,
                    'numero' => $cuatrimestre->cuatrimestre,
                    'total' => $total,
                    'asignadas' => $asignadasCuatrimestre,
                    'pendientes' => max($total - $asignadasCuatrimestre, 0),
                    'porcentaje' => $total > 0 ? (int) round(($asignadasCuatrimestre / $total) * 100) : 0,
                ];
            })
            ->filter(fn (array $item) => $item['total'] > 0)
            ->values()
            ->all();
    }

    /**
     * Se conserva por compatibilidad con llamadas previas del componente.
     * Ahora pasa por la validación de traslapes y por la confirmación preventiva.
     */
    public function asignarProfesor($materia_id, $profesor_id)
    {
        $this->prepararCambioProfesor([(int) $materia_id], $this->normalizarProfesorId($profesor_id), 'individual');
    }

    public function abrirSelectorProfesor($materiaId): void
    {
        $materia = Materia::query()
            ->whereKey((int) $materiaId)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->first();

        if (! $materia) {
            $this->dispatch('asignacion-docente-error', message: 'La materia seleccionada ya no existe o no pertenece a esta licenciatura.');
            return;
        }

        $this->selector_modo = 'individual';
        $this->selector_materia_id = (int) $materia->id;
        $this->dispatch('abrir-selector-profesor');
    }

    public function abrirSelectorProfesorMasivo(): void
    {
        if ($this->filtrar_cuatrimestre === '') {
            $this->dispatch(
                'asignacion-docente-error',
                message: 'Primero selecciona un cuatrimestre. La asignación masiva está limitada a un cuatrimestre por seguridad.'
            );
            return;
        }

        $this->selector_modo = 'masiva';
        $this->selector_materia_id = null;
        $this->dispatch('abrir-selector-profesor');
    }

    public function seleccionarProfesorDesdePicker($profesorId): void
    {
        $profesor = Profesor::query()->find((int) $profesorId);

        if (! $profesor) {
            $this->dispatch('asignacion-docente-error', message: 'El profesor seleccionado ya no existe.');
            return;
        }

        if ($profesor->status !== 'true') {
            $actual = null;

            if ($this->selector_modo === 'individual' && $this->selector_materia_id) {
                $actual = AsignacionMateria::query()
                    ->where('materia_id', $this->selector_materia_id)
                    ->where('licenciatura_id', $this->licenciatura->id)
                    ->where('modalidad_id', $this->modalidad->id)
                    ->value('profesor_id');
            }

            if ((int) $actual !== (int) $profesor->id) {
                $this->dispatch(
                    'asignacion-docente-error',
                    message: 'No se permiten nuevas asignaciones a profesores inactivos.'
                );
                return;
            }
        }

        if ($this->selector_modo === 'masiva') {
            $this->profesor_masivo = (int) $profesor->id;
            $this->dispatch('cerrar-selector-profesor');
            return;
        }

        if ($this->selector_modo !== 'individual' || ! $this->selector_materia_id) {
            $this->dispatch('asignacion-docente-error', message: 'No se pudo determinar la materia a modificar.');
            return;
        }

        $materiaId = (int) $this->selector_materia_id;
        $this->dispatch('cerrar-selector-profesor');
        $this->prepararCambioProfesor([$materiaId], (int) $profesor->id, 'individual');
    }

    public function seleccionarSinProfesorDesdePicker(): void
    {
        if ($this->selector_modo !== 'individual' || ! $this->selector_materia_id) {
            return;
        }

        $materiaId = (int) $this->selector_materia_id;
        $this->dispatch('cerrar-selector-profesor');
        $this->prepararCambioProfesor([$materiaId], null, 'individual');
    }

    public function prepararAsignacionMasiva(): void
    {
        if ($this->filtrar_cuatrimestre === '') {
            $this->dispatch(
                'asignacion-docente-error',
                message: 'Selecciona un cuatrimestre antes de realizar una asignación masiva.'
            );
            return;
        }

        $materiaIds = collect($this->materias_seleccionadas)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($materiaIds->isEmpty()) {
            $this->dispatch('asignacion-docente-error', message: 'Selecciona al menos una materia.');
            return;
        }

        $profesorId = $this->normalizarProfesorId($this->profesor_masivo);

        if (! $profesorId) {
            $this->dispatch('asignacion-docente-error', message: 'Selecciona el profesor que se asignará a las materias marcadas.');
            return;
        }

        $validas = Materia::query()
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('cuatrimestre_id', (int) $this->filtrar_cuatrimestre)
            ->whereIn('id', $materiaIds->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($validas->count() !== $materiaIds->count()) {
            $this->dispatch(
                'asignacion-docente-error',
                message: 'La selección contiene materias fuera del cuatrimestre actual. Limpia la selección y vuelve a intentarlo.'
            );
            return;
        }

        $this->prepararCambioProfesor($validas->all(), $profesorId, 'masiva');
    }

    public function filtrarPorCuatrimestre($cuatrimestreId): void
    {
        $cuatrimestreId = (int) $cuatrimestreId;

        if (! $this->cuatrimestres->contains('id', $cuatrimestreId)) {
            return;
        }

        $this->filtrar_cuatrimestre = (string) $cuatrimestreId;
        $this->materias_seleccionadas = [];
        $this->resetPage();
    }

    public function seleccionarTodoCuatrimestre(): void
    {
        if ($this->filtrar_cuatrimestre === '') {
            $this->dispatch('asignacion-docente-error', message: 'Selecciona un cuatrimestre para marcar todas sus materias.');
            return;
        }

        $this->materias_seleccionadas = Materia::query()
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('cuatrimestre_id', (int) $this->filtrar_cuatrimestre)
            ->orderBy('orden')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function alternarSeleccionPagina(): void
    {
        $idsPagina = $this->materias
            ->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values();

        if ($idsPagina->isEmpty()) {
            return;
        }

        $seleccionadas = collect($this->materias_seleccionadas)
            ->map(fn ($id) => (string) $id)
            ->unique();

        $paginaCompleta = $idsPagina->every(fn (string $id) => $seleccionadas->contains($id));

        if ($paginaCompleta) {
            $this->materias_seleccionadas = $seleccionadas
                ->reject(fn (string $id) => $idsPagina->contains($id))
                ->values()
                ->all();
        } else {
            $this->materias_seleccionadas = $seleccionadas
                ->merge($idsPagina)
                ->unique()
                ->values()
                ->all();
        }
    }

    public function limpiarSeleccionMasiva(): void
    {
        $this->materias_seleccionadas = [];
    }

    public function confirmarOperacionPendiente(): void
    {
        if (empty($this->operacion_pendiente['materia_ids'])) {
            $this->cancelarOperacionPendiente();
            return;
        }

        $materiaIds = collect($this->operacion_pendiente['materia_ids'])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $profesorId = isset($this->operacion_pendiente['profesor_id'])
            ? $this->normalizarProfesorId($this->operacion_pendiente['profesor_id'])
            : null;

        $tipo = $this->operacion_pendiente['tipo'] ?? 'individual';

        $this->ejecutarAsignacion($materiaIds, $profesorId, $tipo);
    }

    public function cancelarOperacionPendiente(): void
    {
        $this->operacion_pendiente = [];
        $this->conflictos_pendientes = [];
        $this->impacto_pendiente = [];
        $this->dispatch('cerrar-confirmacion-asignacion');
    }

    private function prepararCambioProfesor(array $materiaIds, ?int $profesorId, string $tipo): void
    {
        $materiaIds = collect($materiaIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($materiaIds->isEmpty()) {
            $this->dispatch('asignacion-docente-error', message: 'No hay materias válidas para actualizar.');
            return;
        }

        if ($profesorId !== null) {
            $profesor = Profesor::query()->find($profesorId);

            if (! $profesor) {
                $this->dispatch('asignacion-docente-error', message: 'El profesor seleccionado ya no existe.');
                return;
            }

            if ($profesor->status !== 'true') {
                $this->dispatch('asignacion-docente-error', message: 'No se permiten nuevas asignaciones a profesores inactivos.');
                return;
            }
        }

        $materias = Materia::query()
            ->where('licenciatura_id', $this->licenciatura->id)
            ->whereIn('id', $materiaIds->all())
            ->get(['id', 'nombre', 'clave', 'cuatrimestre_id']);

        if ($materias->count() !== $materiaIds->count()) {
            $this->dispatch('asignacion-docente-error', message: 'Una o más materias ya no pertenecen a esta licenciatura.');
            return;
        }

        $asignaciones = AsignacionMateria::query()
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->whereIn('materia_id', $materiaIds->all())
            ->get(['id', 'materia_id', 'cuatrimestre_id', 'profesor_id'])
            ->groupBy('materia_id');

        foreach ($asignaciones as $materiaId => $grupo) {
            if ($grupo->count() > 1) {
                $this->dispatch(
                    'asignacion-docente-error',
                    message: "La materia ID {$materiaId} tiene asignaciones duplicadas. Debe corregirse antes de cambiar el profesor."
                );
                return;
            }
        }

        $materiasConCambio = $materias
            ->filter(function ($materia) use ($asignaciones, $profesorId) {
                $asignacion = $asignaciones->get($materia->id)?->first();
                return (int) ($asignacion?->profesor_id ?? 0) !== (int) ($profesorId ?? 0);
            })
            ->values();

        if ($materiasConCambio->isEmpty()) {
            $this->dispatch('asignacion-docente-actualizada', message: 'No hay cambios por guardar.');
            return;
        }

        $asignacionIds = $materiasConCambio
            ->map(fn ($materia) => $asignaciones->get($materia->id)?->first()?->id)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($profesorId === null && ! empty($asignacionIds)) {
            $capturas = CalificacionDocenteCaptura::query()
                ->whereIn('asignacion_materia_id', $asignacionIds)
                ->count();

            if ($capturas > 0) {
                $this->dispatch(
                    'asignacion-docente-error',
                    message: 'No puedes dejar sin profesor una materia que ya tiene capturas del módulo Calificaciones por docente. Reasígnala directamente a otro profesor.'
                );
                return;
            }
        }

        $conflictos = [];

        if ($profesorId !== null && ! empty($asignacionIds)) {
            $conflictos = app(HorarioTraslapeService::class)
                ->conflictosParaCambioProfesor($profesorId, (int) $this->modalidad->id, $asignacionIds);
        }

        $impacto = $this->calcularImpacto($asignacionIds);
        $requiereConfirmacion = ! empty($conflictos)
            || $impacto['calificaciones'] > 0
            || $impacto['capturas'] > 0;

        $idsCambio = $materiasConCambio->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($requiereConfirmacion) {
            $this->operacion_pendiente = [
                'tipo' => $tipo,
                'materia_ids' => $idsCambio,
                'profesor_id' => $profesorId,
            ];
            $this->conflictos_pendientes = $conflictos;
            $this->impacto_pendiente = $impacto;
            $this->dispatch('asignacion-requiere-confirmacion');
            return;
        }

        $this->ejecutarAsignacion($idsCambio, $profesorId, $tipo);
    }

    private function calcularImpacto(array $asignacionIds): array
    {
        if (empty($asignacionIds)) {
            return [
                'horarios' => 0,
                'calificaciones' => 0,
                'capturas' => 0,
                'entregadas' => 0,
                'validadas' => 0,
            ];
        }

        return [
            'horarios' => Horario::query()->whereIn('asignacion_materia_id', $asignacionIds)->count(),
            'calificaciones' => Calificacion::query()->whereIn('asignacion_materia_id', $asignacionIds)->count(),
            'capturas' => CalificacionDocenteCaptura::query()->whereIn('asignacion_materia_id', $asignacionIds)->count(),
            'entregadas' => CalificacionDocenteCaptura::query()
                ->whereIn('asignacion_materia_id', $asignacionIds)
                ->where('estado', 'entregada')
                ->count(),
            'validadas' => CalificacionDocenteCaptura::query()
                ->whereIn('asignacion_materia_id', $asignacionIds)
                ->where('estado', 'validada')
                ->count(),
        ];
    }

    private function ejecutarAsignacion(array $materiaIds, ?int $profesorId, string $tipo): void
    {
        try {
            $materias = Materia::query()
                ->where('licenciatura_id', $this->licenciatura->id)
                ->whereIn('id', $materiaIds)
                ->get(['id', 'nombre', 'clave', 'cuatrimestre_id']);

            if ($materias->count() !== count($materiaIds)) {
                throw new RuntimeException('Una o más materias dejaron de estar disponibles durante la operación.');
            }

            $resultado = DB::transaction(function () use ($materias, $profesorId) {
                $cambios = 0;
                $auditoria = [];

                foreach ($materias as $materia) {
                    $criterios = [
                        'materia_id' => (int) $materia->id,
                        'licenciatura_id' => (int) $this->licenciatura->id,
                        'modalidad_id' => (int) $this->modalidad->id,
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

                    // Importante: NO se elimina la asignación al dejarla sin profesor.
                    // El borrado cascada eliminaría horarios y calificaciones vinculadas.
                    $asignacion->profesor_id = $profesorId;
                    $asignacion->save();
                    $cambios++;

                    $auditoria[] = [
                        'user_id' => auth()->id(),
                        'asignacion_materia_id' => $asignacion->id,
                        'materia_id' => $materia->id,
                        'licenciatura_id' => $this->licenciatura->id,
                        'modalidad_id' => $this->modalidad->id,
                        'cuatrimestre_id' => $materia->cuatrimestre_id,
                        'profesor_anterior_id' => $profesorAnteriorId,
                        'profesor_nuevo_id' => $profesorId,
                    ];
                }

                return [
                    'cambios' => $cambios,
                    'auditoria' => $auditoria,
                ];
            });

            $cambios = (int) ($resultado['cambios'] ?? 0);

            foreach (($resultado['auditoria'] ?? []) as $registroAuditoria) {
                Log::info('Cambio de profesor en asignación de materia', $registroAuditoria);
            }

            foreach ($materiaIds as $materiaId) {
                $this->profesor_seleccionado[(int) $materiaId] = $profesorId ?? '';
            }

            if ($tipo === 'masiva') {
                $this->materias_seleccionadas = [];
            }

            $this->operacion_pendiente = [];
            $this->conflictos_pendientes = [];
            $this->impacto_pendiente = [];
            $this->dispatch('cerrar-confirmacion-asignacion');
            $this->dispatch('cerrar-selector-profesor');

            if ($cambios === 0) {
                $mensaje = 'No había cambios por guardar.';
            } elseif ($profesorId === null) {
                $mensaje = $cambios === 1
                    ? 'La materia quedó sin profesor, conservando sus horarios y calificaciones.'
                    : "{$cambios} materias quedaron sin profesor, conservando sus registros relacionados.";
            } else {
                $profesor = $this->profesores->firstWhere('id', $profesorId);
                $nombreProfesor = $profesor ? $this->nombreProfesor($profesor) : 'el profesor seleccionado';
                $mensaje = $cambios === 1
                    ? "Profesor asignado: {$nombreProfesor}."
                    : "{$cambios} materias asignadas a {$nombreProfesor}.";
            }

            $this->dispatch('asignacion-docente-actualizada', message: $mensaje);
        } catch (Throwable $exception) {
            report($exception);

            $this->dispatch(
                'asignacion-docente-error',
                message: 'No se pudo guardar la asignación. ' . $exception->getMessage()
            );
        }
    }

    private function normalizarProfesorId($profesorId): ?int
    {
        return is_numeric($profesorId) && (int) $profesorId > 0
            ? (int) $profesorId
            : null;
    }

    private function nombreProfesor($profesor): string
    {
        return trim(collect([
            $profesor->apellido_paterno ?? null,
            $profesor->apellido_materno ?? null,
            $profesor->nombre ?? null,
        ])->filter()->implode(' '));
    }

    public function cargarProfesoresAsignados($materias): void
    {
        $this->profesor_seleccionado = [];

        foreach ($materias as $materia) {
            $asignacion = $materia->asignacionMaterias->first();
            $this->profesor_seleccionado[$materia->id] = $asignacion?->profesor_id ?? '';
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'search',
            'filtrar_cuatrimestre',
            'filtrar_asignacion',
            'filtrar_profesor',
            'filtrar_calificable',
            'profesor_masivo',
        ]);

        $this->materias_seleccionadas = [];
        $this->por_pagina = 20;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiltrarCuatrimestre(): void
    {
        // Evita aplicar accidentalmente una selección hecha en otro cuatrimestre.
        $this->materias_seleccionadas = [];
        $this->resetPage();
    }

    public function updatedFiltrarAsignacion($value): void
    {
        if ($value === 'sin_asignar') {
            $this->filtrar_profesor = '';
        }

        $this->resetPage();
    }

    public function updatedFiltrarProfesor($value): void
    {
        if ($value !== '') {
            $this->filtrar_asignacion = 'asignadas';
        }

        $this->resetPage();
    }

    public function updatedFiltrarCalificable(): void
    {
        $this->resetPage();
    }

    public function updatedPorPagina(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $materias = $this->materias;
        $this->cargarProfesoresAsignados($materias);

        return view('livewire.admin.licenciaturas.submodulo.asignar-materia', [
            'materias' => $materias,
            'resumen' => $this->resumen,
            'progresoCuatrimestres' => $this->progresoCuatrimestres,
        ]);
    }
}
