<?php

namespace App\Livewire\Admin\AsignacionDocente;

use App\Models\AsignacionMateria;
use App\Models\AsignarGeneracion;
use App\Models\Dashboard;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\Mes;
use App\Models\Periodo;
use App\Models\Profesor;
use App\Services\AsignacionDocenteService;
use App\Services\HorarioTraslapeService;
use Illuminate\Support\Collection;
use Livewire\Component;

class Panel extends Component
{
    public string $busqueda = '';
    public string $filtro_licenciatura = '';
    public string $filtro_modalidad = '';
    public string $filtro_cuatrimestre = '';
    public string $filtro_estado = '';
    public string $filtro_profesor = '';

    public array $seleccionadas = [];
    public string $profesor_destino = '';
    public ?string $objetivo_individual = null;
    public string $modo_operacion = 'masiva';

    public array $operacion_pendiente = [];
    public array $conflictos_pendientes = [];
    public array $impacto_pendiente = [];

    public string $ciclo_escolar = '';
    public string $periodo_escolar = '';

    public function mount(): void
    {
        $dashboard = Dashboard::query()->latest('id')->first();
        $this->ciclo_escolar = (string) ($dashboard?->ciclo_escolar ?? '');
        $this->periodo_escolar = (string) ($dashboard?->periodo_escolar ?? '');
    }

    public function getProfesoresProperty(): Collection
    {
        return Profesor::query()
            ->orderByRaw("CASE WHEN status = 'true' THEN 0 ELSE 1 END")
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'perfil', 'color', 'status']);
    }

    public function getContextosProperty(): Collection
    {
        if ($this->ciclo_escolar === '') {
            return collect();
        }

        $mesId = $this->periodo_escolar !== ''
            ? Mes::query()->where('meses_corto', $this->periodo_escolar)->value('id')
            : null;

        $periodos = Periodo::query()
            ->with(['generacion:id,generacion', 'cuatrimestre:id,cuatrimestre,nombre_cuatrimestre'])
            ->where('ciclo_escolar', $this->ciclo_escolar)
            ->when($mesId, fn ($q) => $q->where('mes_id', $mesId))
            ->get();

        if ($periodos->isEmpty()) {
            return collect();
        }

        $periodosPorGeneracion = $periodos->groupBy('generacion_id');
        $generacionIds = $periodos->pluck('generacion_id')->unique()->values();

        $asignacionesGeneracion = AsignarGeneracion::query()
            ->with([
                'licenciatura:id,nombre,nombre_corto',
                'modalidad:id,nombre',
                'generacion:id,generacion',
            ])
            ->whereIn('generacion_id', $generacionIds->all())
            ->get();

        $inscripciones = Inscripcion::query()
            ->where('status', 'true')
            ->where('egresado', 'false')
            ->whereIn('generacion_id', $generacionIds->all())
            ->get(['licenciatura_id', 'modalidad_id', 'generacion_id'])
            ->groupBy(fn ($a) => implode(':', [
                (int) $a->licenciatura_id,
                (int) $a->modalidad_id,
                (int) $a->generacion_id,
            ]));

        return $asignacionesGeneracion
            ->flatMap(function (AsignarGeneracion $asignacion) use ($periodosPorGeneracion, $inscripciones) {
                return collect($periodosPorGeneracion->get($asignacion->generacion_id, collect()))
                    ->map(function ($periodo) use ($asignacion, $inscripciones) {
                        // 'Con alumnos activos' se evalúa por licenciatura + modalidad + generación.
                        // El cuatrimestre mostrado lo determina Periodo; no se exige que la inscripción
                        // ya haya sido promovida a ese cuatrimestre para que la carga académica aparezca.
                        $claveAlumno = implode(':', [
                            (int) $asignacion->licenciatura_id,
                            (int) $asignacion->modalidad_id,
                            (int) $asignacion->generacion_id,
                        ]);

                        if (! $inscripciones->has($claveAlumno)) {
                            return null;
                        }

                        return [
                            'licenciatura_id' => (int) $asignacion->licenciatura_id,
                            'licenciatura' => $asignacion->licenciatura?->nombre ?? 'Licenciatura',
                            'licenciatura_corta' => $asignacion->licenciatura?->nombre_corto ?: ($asignacion->licenciatura?->nombre ?? 'Lic.'),
                            'modalidad_id' => (int) $asignacion->modalidad_id,
                            'modalidad' => $asignacion->modalidad?->nombre ?? 'Modalidad',
                            'generacion_id' => (int) $asignacion->generacion_id,
                            'generacion' => $asignacion->generacion?->generacion ?? '—',
                            'cuatrimestre_id' => (int) $periodo->cuatrimestre_id,
                            'cuatrimestre' => $periodo->cuatrimestre?->cuatrimestre ?? $periodo->cuatrimestre_id,
                            'cuatrimestre_nombre' => $periodo->cuatrimestre?->nombre_cuatrimestre ?? 'Cuatrimestre',
                            'alumnos' => $inscripciones->get($claveAlumno)->count(),
                        ];
                    });
            })
            ->filter()
            ->unique(fn (array $c) => implode(':', [
                $c['licenciatura_id'], $c['modalidad_id'], $c['generacion_id'], $c['cuatrimestre_id'],
            ]))
            ->sortBy(fn (array $c) => sprintf('%03d-%03d-%03d-%03d', $c['cuatrimestre_id'], $c['licenciatura_id'], $c['modalidad_id'], $c['generacion_id']))
            ->values();
    }

    public function getFilasBaseProperty(): Collection
    {
        $contextos = $this->contextos;
        if ($contextos->isEmpty()) {
            return collect();
        }

        $combinaciones = $contextos
            ->map(fn (array $c) => [$c['licenciatura_id'], $c['cuatrimestre_id']])
            ->unique(fn (array $p) => $p[0] . ':' . $p[1])
            ->values();

        $materiasQuery = Materia::query()
            ->with(['licenciatura:id,nombre,nombre_corto', 'cuatrimestre:id,cuatrimestre,nombre_cuatrimestre'])
            ->where(function ($q) use ($combinaciones) {
                foreach ($combinaciones as [$licenciaturaId, $cuatrimestreId]) {
                    $q->orWhere(fn ($p) => $p->where('licenciatura_id', $licenciaturaId)->where('cuatrimestre_id', $cuatrimestreId));
                }
            })
            ->orderBy('licenciatura_id')
            ->orderBy('cuatrimestre_id')
            ->orderBy('orden')
            ->orderBy('nombre');

        $materias = $materiasQuery->get(['id', 'nombre', 'clave', 'licenciatura_id', 'cuatrimestre_id', 'orden', 'calificable']);
        $modalidadIds = $contextos->pluck('modalidad_id')->unique()->values();

        $asignaciones = AsignacionMateria::query()
            ->with('profesor:id,nombre,apellido_paterno,apellido_materno,color,status')
            ->whereIn('materia_id', $materias->pluck('id')->all())
            ->whereIn('modalidad_id', $modalidadIds->all())
            ->get()
            ->groupBy(fn ($a) => $a->materia_id . ':' . $a->modalidad_id);

        return $contextos
            ->groupBy(fn (array $c) => implode(':', [$c['licenciatura_id'], $c['modalidad_id'], $c['cuatrimestre_id']]))
            ->flatMap(function (Collection $grupoContextos, string $claveContexto) use ($materias, $asignaciones) {
                $primero = $grupoContextos->first();
                [$licenciaturaId, $modalidadId, $cuatrimestreId] = array_map('intval', explode(':', $claveContexto));

                return $materias
                    ->where('licenciatura_id', $licenciaturaId)
                    ->where('cuatrimestre_id', $cuatrimestreId)
                    ->map(function (Materia $materia) use ($grupoContextos, $primero, $modalidadId, $asignaciones) {
                        $clave = $materia->id . ':' . $modalidadId;
                        /** @var Collection<int,AsignacionMateria> $grupoAsignaciones */
                        $grupoAsignaciones = $asignaciones->get($clave, collect());
                        $asignacionesValidas = $grupoAsignaciones->filter(fn (AsignacionMateria $a) =>
                            (int) $a->licenciatura_id === (int) $materia->licenciatura_id
                            && (int) $a->cuatrimestre_id === (int) $materia->cuatrimestre_id
                        )->values();
                        $duplicada = $asignacionesValidas->count() > 1 || $grupoAsignaciones->count() !== $asignacionesValidas->count();
                        $asignacion = $asignacionesValidas->first();
                        $profesor = $asignacion?->profesor;

                        $estado = $duplicada ? 'duplicada' : ($profesor ? 'asignada' : 'pendiente');

                        return [
                            'clave_objetivo' => $clave,
                            'materia_id' => (int) $materia->id,
                            'materia' => $materia->nombre,
                            'clave_materia' => $materia->clave,
                            'orden' => (int) ($materia->orden ?? 0),
                            'calificable' => $materia->calificable,
                            'licenciatura_id' => (int) $materia->licenciatura_id,
                            'licenciatura' => $materia->licenciatura?->nombre ?? $primero['licenciatura'],
                            'licenciatura_corta' => $materia->licenciatura?->nombre_corto ?: ($materia->licenciatura?->nombre ?? $primero['licenciatura_corta']),
                            'modalidad_id' => (int) $modalidadId,
                            'modalidad' => $primero['modalidad'],
                            'cuatrimestre_id' => (int) $materia->cuatrimestre_id,
                            'cuatrimestre' => $materia->cuatrimestre?->cuatrimestre ?? $primero['cuatrimestre'],
                            'generaciones' => $grupoContextos->pluck('generacion')->unique()->values()->all(),
                            'generacion_ids' => $grupoContextos->pluck('generacion_id')->unique()->values()->all(),
                            'alumnos' => (int) $grupoContextos->sum('alumnos'),
                            'asignacion_id' => $asignacion?->id ? (int) $asignacion->id : null,
                            'profesor_id' => $profesor?->id ? (int) $profesor->id : null,
                            'profesor' => $profesor ? $this->nombreProfesor($profesor) : null,
                            'profesor_color' => $profesor?->color ?: '#cbd5e1',
                            'profesor_activo' => $profesor?->status === 'true',
                            'estado' => $estado,
                        ];
                    });
            })
            ->sortBy(fn (array $f) => sprintf('%03d-%05d-%03d-%05d', $f['cuatrimestre_id'], $f['licenciatura_id'], $f['modalidad_id'], $f['orden']))
            ->values();
    }

    public function getFilasProperty(): Collection
    {
        $termino = mb_strtolower(trim($this->busqueda), 'UTF-8');

        return $this->filasBase
            ->filter(function (array $f) use ($termino) {
                if ($termino !== '') {
                    $hay = mb_stripos($f['materia'], $termino, 0, 'UTF-8') !== false
                        || mb_stripos((string) $f['clave_materia'], $termino, 0, 'UTF-8') !== false
                        || mb_stripos($f['licenciatura'], $termino, 0, 'UTF-8') !== false
                        || mb_stripos((string) ($f['profesor'] ?? ''), $termino, 0, 'UTF-8') !== false;
                    if (! $hay) return false;
                }

                if ($this->filtro_licenciatura !== '' && (int) $this->filtro_licenciatura !== $f['licenciatura_id']) return false;
                if ($this->filtro_modalidad !== '' && (int) $this->filtro_modalidad !== $f['modalidad_id']) return false;
                if ($this->filtro_cuatrimestre !== '' && (int) $this->filtro_cuatrimestre !== $f['cuatrimestre_id']) return false;
                if ($this->filtro_estado !== '' && $this->filtro_estado !== $f['estado']) return false;
                if ($this->filtro_profesor !== '' && (int) $this->filtro_profesor !== (int) ($f['profesor_id'] ?? 0)) return false;

                return true;
            })
            ->values();
    }

    public function getResumenProperty(): array
    {
        $filas = $this->filasBase;
        return [
            'total' => $filas->count(),
            'asignadas' => $filas->where('estado', 'asignada')->count(),
            'pendientes' => $filas->where('estado', 'pendiente')->count(),
            'duplicadas' => $filas->where('estado', 'duplicada')->count(),
            'licenciaturas' => $filas->pluck('licenciatura_id')->unique()->count(),
        ];
    }

    public function getOpcionesFiltrosProperty(): array
    {
        $filas = $this->filasBase;
        return [
            'licenciaturas' => $filas->map(fn ($f) => ['id' => $f['licenciatura_id'], 'nombre' => $f['licenciatura']])->unique('id')->sortBy('nombre')->values(),
            'modalidades' => $filas->map(fn ($f) => ['id' => $f['modalidad_id'], 'nombre' => $f['modalidad']])->unique('id')->sortBy('nombre')->values(),
            'cuatrimestres' => $filas->map(fn ($f) => ['id' => $f['cuatrimestre_id'], 'nombre' => $f['cuatrimestre'] . '° Cuatrimestre'])->unique('id')->sortBy('id')->values(),
        ];
    }

    public function alternarSeleccionFiltrada(): void
    {
        $ids = $this->filas->where('estado', '!=', 'duplicada')->pluck('clave_objetivo')->values();
        $actual = collect($this->seleccionadas)->unique();
        $todos = $ids->isNotEmpty() && $ids->every(fn ($id) => $actual->contains($id));

        $this->seleccionadas = $todos
            ? $actual->reject(fn ($id) => $ids->contains($id))->values()->all()
            : $actual->merge($ids)->unique()->values()->all();
    }

    public function limpiarSeleccion(): void
    {
        $this->seleccionadas = [];
    }

    public function abrirAsignacionMasiva(): void
    {
        if (empty($this->seleccionadas)) {
            $this->dispatch('asignacion-global-error', message: 'Selecciona al menos una materia.');
            return;
        }

        $this->modo_operacion = 'masiva';
        $this->objetivo_individual = null;
        $this->profesor_destino = '';
        $this->dispatch('abrir-asignacion-global');
    }

    public function abrirAsignacionIndividual(string $claveObjetivo): void
    {
        $fila = $this->filasBase->firstWhere('clave_objetivo', $claveObjetivo);
        if (! $fila || $fila['estado'] === 'duplicada') {
            $this->dispatch('asignacion-global-error', message: 'La materia seleccionada no está disponible para asignación.');
            return;
        }

        $this->modo_operacion = 'individual';
        $this->objetivo_individual = $claveObjetivo;
        $this->profesor_destino = $fila['profesor_id'] ? (string) $fila['profesor_id'] : '';
        $this->dispatch('abrir-asignacion-global');
    }

    public function prepararAsignacion(): void
    {
        $profesorId = $this->normalizarProfesorId($this->profesor_destino);
        if (! $profesorId) {
            $this->dispatch('asignacion-global-error', message: 'Selecciona un profesor activo.');
            return;
        }

        $claves = $this->modo_operacion === 'individual'
            ? array_filter([$this->objetivo_individual])
            : $this->seleccionadas;

        $objetivos = $this->objetivosDesdeClaves($claves);
        if (empty($objetivos)) {
            $this->dispatch('asignacion-global-error', message: 'No hay materias válidas para actualizar.');
            return;
        }

        try {
            $preparacion = app(AsignacionDocenteService::class)->preparar($objetivos, $profesorId);

            if ($preparacion['sin_cambios']) {
                $this->dispatch('cerrar-asignacion-global');
                $this->dispatch('asignacion-global-ok', message: 'No había cambios por guardar.');
                return;
            }

            if ($preparacion['requiere_confirmacion']) {
                $this->operacion_pendiente = [
                    'objetivos' => $preparacion['objetivos'],
                    'profesor_id' => $profesorId,
                ];
                $this->conflictos_pendientes = $preparacion['conflictos'];
                $this->impacto_pendiente = $preparacion['impacto'];
                $this->dispatch('cerrar-asignacion-global');
                $this->dispatch('abrir-confirmacion-global');
                return;
            }

            $this->ejecutar($preparacion['objetivos'], $profesorId);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('asignacion-global-error', message: $e->getMessage());
        }
    }

    public function confirmarOperacion(): void
    {
        if (empty($this->operacion_pendiente['objetivos'])) return;
        $this->ejecutar(
            $this->operacion_pendiente['objetivos'],
            $this->normalizarProfesorId($this->operacion_pendiente['profesor_id'] ?? null)
        );
    }

    public function cancelarOperacion(): void
    {
        $this->operacion_pendiente = [];
        $this->conflictos_pendientes = [];
        $this->impacto_pendiente = [];
        $this->dispatch('cerrar-confirmacion-global');
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['busqueda', 'filtro_licenciatura', 'filtro_modalidad', 'filtro_cuatrimestre', 'filtro_estado', 'filtro_profesor']);
        $this->seleccionadas = [];
    }

    public function getResumenProfesorProperty(): ?array
    {
        $profesorId = $this->normalizarProfesorId($this->profesor_destino);
        if (! $profesorId) return null;

        $profesor = $this->profesores->firstWhere('id', $profesorId);
        if (! $profesor) return null;

        $filas = $this->filasBase->where('profesor_id', $profesorId)->values();
        $asignacionIds = $filas->pluck('asignacion_id')->filter()->unique()->values();
        $horarios = Horario::query()
            ->with(['dia:id,dia', 'licenciatura:id,nombre,nombre_corto', 'generacion:id,generacion', 'asignacionMateria.materia:id,nombre,clave'])
            ->whereIn('asignacion_materia_id', $asignacionIds->all())
            ->get();

        $minutos = $horarios->sum(fn ($h) => $this->minutosRango($h->hora));
        $traslapes = 0;
        $servicio = app(HorarioTraslapeService::class);

        foreach ($horarios->groupBy(fn ($h) => $h->modalidad_id . ':' . $h->dia_id) as $grupo) {
            $grupo = $grupo->values();
            for ($i = 0; $i < $grupo->count(); $i++) {
                for ($j = $i + 1; $j < $grupo->count(); $j++) {
                    if ((int) $grupo[$i]->asignacion_materia_id === (int) $grupo[$j]->asignacion_materia_id) continue;
                    if ($servicio->rangosSeTraslapan($grupo[$i]->hora, $grupo[$j]->hora)) $traslapes++;
                }
            }
        }

        return [
            'nombre' => $this->nombreProfesor($profesor),
            'perfil' => $profesor->perfil,
            'activo' => $profesor->status === 'true',
            'materias' => $filas->count(),
            'licenciaturas' => $filas->pluck('licenciatura_id')->unique()->count(),
            'horas' => sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60),
            'traslapes' => $traslapes,
            'carga' => $filas->take(8)->map(fn ($f) => $f['materia'] . ' · ' . $f['licenciatura_corta'])->all(),
        ];
    }

    private function ejecutar(array $objetivos, ?int $profesorId): void
    {
        try {
            $resultado = app(AsignacionDocenteService::class)->ejecutar($objetivos, $profesorId, auth()->id());
            $cambios = (int) ($resultado['cambios'] ?? 0);

            $this->seleccionadas = [];
            $this->operacion_pendiente = [];
            $this->conflictos_pendientes = [];
            $this->impacto_pendiente = [];
            $this->dispatch('cerrar-asignacion-global');
            $this->dispatch('cerrar-confirmacion-global');
            $this->dispatch('asignacion-global-ok', message: $cambios === 1 ? 'Asignación docente actualizada.' : "{$cambios} asignaciones docentes actualizadas.");
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('asignacion-global-error', message: 'No se pudo guardar la asignación. ' . $e->getMessage());
        }
    }

    /** @param array<int,string> $claves */
    private function objetivosDesdeClaves(array $claves): array
    {
        $validas = $this->filasBase->keyBy('clave_objetivo');

        return collect($claves)
            ->unique()
            ->map(function ($clave) use ($validas) {
                $fila = $validas->get($clave);
                if (! $fila || $fila['estado'] === 'duplicada') return null;
                return ['materia_id' => $fila['materia_id'], 'modalidad_id' => $fila['modalidad_id']];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function nombreProfesor($profesor): string
    {
        return trim(collect([$profesor->apellido_paterno, $profesor->apellido_materno, $profesor->nombre])->filter()->implode(' '));
    }

    private function normalizarProfesorId($id): ?int
    {
        return is_numeric($id) && (int) $id > 0 ? (int) $id : null;
    }

    private function minutosRango(?string $rango): int
    {
        [$inicio, $fin] = array_pad(array_map('trim', explode('-', strtolower((string) $rango), 2)), 2, null);
        if (! $inicio || ! $fin) return 0;
        $a = strtotime($inicio); $b = strtotime($fin);
        if ($a === false || $b === false) return 0;
        $mi = ((int) date('G', $a) * 60) + (int) date('i', $a);
        $mf = ((int) date('G', $b) * 60) + (int) date('i', $b);
        return max($mf - $mi, 0);
    }

    public function render()
    {
        return view('livewire.admin.asignacion-docente.panel', [
            'filas' => $this->filas,
            'resumen' => $this->resumen,
            'opciones' => $this->opcionesFiltros,
            'profesores' => $this->profesores,
            'resumenProfesor' => $this->resumenProfesor,
        ]);
    }
}
