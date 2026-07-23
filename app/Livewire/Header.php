<?php

namespace App\Livewire;

use App\Exports\MatriculasReporteExport;
use App\Models\Cuatrimestre;
use App\Models\Dashboard;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\Profesor;
use App\Services\MatriculaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class Header extends Component
{
    use WithPagination;

    public $dashboard;

    public bool $open = false;

    public int $total = 0;

    public int $conMatricula = 0;

    public int $sinMatricula = 0;

    public int $matriculasVacias = 0;

    public int $matriculasIncorrectas = 0;

    public int $matriculasDuplicadas = 0;

    public int $porcConMatricula = 0;

    public int $porcSinMatricula = 0;

    public int $bajos = 0;

    public int $porcBajos = 0;

    public bool $modalOpen = false;

    public string $modalTipo = 'con'; // con | sin | bajos | todos

    public string $sinCategoria = 'todos'; // todos | vacias | formato | duplicadas

    public string $search = '';

    public string $licenciaturaId = '';

    public string $modalidadId = '';

    public string $generacionId = '';

    public string $cuatrimestreId = '';

    public string $sexo = '';

    public string $residencia = '';

    public string $estadoAlumno = '';

    public string $generacionEstado = 'activas'; // activas | finalizadas | todas

    public ?string $fechaDesde = null;

    public ?string $fechaHasta = null;

    public string $materiaId = '';

    public string $profesorId = '';

    public string $cuatrimestreAcademicoId = '';

    public string $riesgoTipo = 'todos'; // todos | numerica | np

    public int $perPage = 25;

    public array $selectedIds = [];

    public array $excludedIds = [];

    public bool $selectAllFiltered = false;

    private const FILTER_PROPERTIES = [
        'search',
        'licenciaturaId',
        'modalidadId',
        'generacionId',
        'cuatrimestreId',
        'sexo',
        'residencia',
        'estadoAlumno',
        'generacionEstado',
        'fechaDesde',
        'fechaHasta',
        'materiaId',
        'profesorId',
        'cuatrimestreAcademicoId',
        'riesgoTipo',
        'sinCategoria',
    ];

    public function mount(): void
    {
        $this->modalOpen = false;
        $this->cargarDashboard();
        $this->cargarEstadisticas();
    }

    #[On('refreshHeader')]
    public function refreshHeader(): void
    {
        $this->cargarDashboard();
        $this->cargarEstadisticas();
    }

    public function openModal(string $tipo = 'con'): void
    {
        $this->modalTipo = in_array($tipo, ['con', 'sin', 'bajos', 'todos'], true) ? $tipo : 'con';
        $this->resetFiltros();
        $this->modalOpen = true;
        $this->open = false;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetSelection();
        $this->resetPage('matriculasPage');
    }

    public function resetFiltros(): void
    {
        $this->reset([
            'search',
            'licenciaturaId',
            'modalidadId',
            'generacionId',
            'cuatrimestreId',
            'sexo',
            'residencia',
            'estadoAlumno',
            'fechaDesde',
            'fechaHasta',
            'materiaId',
            'profesorId',
            'cuatrimestreAcademicoId',
        ]);

        $this->generacionEstado = 'activas';
        $this->riesgoTipo = 'todos';
        $this->sinCategoria = 'todos';
        $this->perPage = 25;
        $this->resetSelection();
        $this->resetPage('matriculasPage');
    }

    public function updated(string $property): void
    {
        if (in_array($property, self::FILTER_PROPERTIES, true)) {
            $this->resetSelection();
            $this->resetPage('matriculasPage');
        }
    }

    public function updatedLicenciaturaId(): void
    {
        $this->modalidadId = '';
        $this->generacionId = '';
        $this->cuatrimestreId = '';
    }

    public function updatedModalidadId(): void
    {
        $this->generacionId = '';
        $this->cuatrimestreId = '';
    }

    public function updatedGeneracionId(): void
    {
        $this->cuatrimestreId = '';
    }

    public function updatedGeneracionEstado(): void
    {
        $this->generacionId = '';
        $this->cuatrimestreId = '';
    }

    public function updatedPerPage($value): void
    {
        $value = (int) $value;
        $this->perPage = in_array($value, [25, 50, 100], true) ? $value : 25;
        $this->resetPage('matriculasPage');
    }

    public function toggleSeleccion(int $id): void
    {
        if ($this->selectAllFiltered) {
            $this->excludedIds = $this->toggleId($this->excludedIds, $id);

            return;
        }

        $this->selectedIds = $this->toggleId($this->selectedIds, $id);
    }

    public function togglePagina(string $ids): void
    {
        $pageIds = collect(explode(',', $ids))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($pageIds->isEmpty()) {
            return;
        }

        $allSelected = $pageIds->every(fn (int $id) => $this->isSelected($id));

        foreach ($pageIds as $id) {
            if ($this->selectAllFiltered) {
                if ($allSelected && ! in_array($id, $this->excludedIds, true)) {
                    $this->excludedIds[] = $id;
                } elseif (! $allSelected) {
                    $this->excludedIds = array_values(array_diff($this->excludedIds, [$id]));
                }
            } elseif ($allSelected) {
                $this->selectedIds = array_values(array_diff($this->selectedIds, [$id]));
            } elseif (! in_array($id, $this->selectedIds, true)) {
                $this->selectedIds[] = $id;
            }
        }

        $this->selectedIds = array_values(array_unique(array_map('intval', $this->selectedIds)));
        $this->excludedIds = array_values(array_unique(array_map('intval', $this->excludedIds)));
    }

    public function toggleSelectAllFiltered(): void
    {
        $this->selectAllFiltered = ! $this->selectAllFiltered;
        $this->selectedIds = [];
        $this->excludedIds = [];
    }

    public function isSelected(int $id): bool
    {
        if ($this->selectAllFiltered) {
            return ! in_array($id, $this->excludedIds, true);
        }

        return in_array($id, $this->selectedIds, true);
    }

    public function editarAlumno(int $id): void
    {
        $this->autorizarAdministracion();
        $this->modalOpen = false;
        $this->dispatch('abrirEstudiante', id: $id);
    }

    public function generarMatricula(int $id, MatriculaService $servicio): void
    {
        $this->autorizarAdministracion();

        try {
            $alumno = Inscripcion::query()->findOrFail($id);
            $matricula = $servicio->generarPara($alumno);

            $this->cargarEstadisticas();
            $this->resetSelection();

            $this->dispatch('swal', [
                'title' => 'Matrícula generada correctamente',
                'text' => $matricula,
                'icon' => 'success',
                'position' => 'top-end',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $this->dispatch('swal', [
                'title' => 'No fue posible generar la matrícula',
                'text' => $exception->getMessage(),
                'icon' => 'error',
                'position' => 'top-end',
            ]);
        }
    }

    public function exportar(string $formato, string $alcance = 'filtrados')
    {
        $this->autorizarAdministracion();

        abort_unless(in_array($formato, ['excel', 'pdf'], true), 404);
        abort_unless(in_array($alcance, ['filtrados', 'seleccionados'], true), 404);

        $query = $this->construirConsultaAlumnos(true);

        if ($alcance === 'seleccionados') {
            $this->aplicarSeleccion($query);
        }

        $alumnos = $query->get();

        if ($alumnos->isEmpty()) {
            $this->dispatch('swal', [
                'title' => 'No hay registros para exportar',
                'icon' => 'warning',
                'position' => 'top-end',
            ]);

            return null;
        }

        $sufijo = now()->format('Ymd_His');
        $nombreBase = 'matriculas_' . $this->modalTipo . '_' . $sufijo;

        if ($formato === 'excel') {
            return Excel::download(
                new MatriculasReporteExport($alumnos, $this->modalTipo),
                $nombreBase . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('pdf.matriculas-reporte', [
            'titulo' => $this->tituloModal(),
            'tipo' => $this->modalTipo,
            'alumnos' => $alumnos,
            'filtros' => $this->resumenFiltros(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($nombreBase . '.pdf');
    }

    public function matriculaEstado(Inscripcion $alumno): string
    {
        $matricula = app(MatriculaService::class)->normalizar($alumno->matricula);

        if ($matricula === '') {
            return 'vacia';
        }

        if ((int) ($alumno->matricula_coincidencias ?? 1) > 1) {
            return 'duplicada';
        }

        return app(MatriculaService::class)->esValida($matricula) ? 'valida' : 'formato';
    }

    public function modalidadChip(?string $nombre): string
    {
        $nombre = mb_strtolower(trim((string) $nombre), 'UTF-8');

        return match (true) {
            str_contains($nombre, 'escolar') =>
                'bg-sky-50 text-sky-700 ring-1 ring-sky-200 dark:bg-sky-900/20 dark:text-sky-200 dark:ring-sky-800/60',
            str_contains($nombre, 'sabat') =>
                'bg-rose-50 text-rose-700 ring-1 ring-rose-200 dark:bg-rose-900/20 dark:text-rose-200 dark:ring-rose-800/60',
            str_contains($nombre, 'mixt') =>
                'bg-amber-50 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-200 dark:ring-amber-800/60',
            str_contains($nombre, 'línea'), str_contains($nombre, 'linea'), str_contains($nombre, 'online'), str_contains($nombre, 'virtual') =>
                'bg-purple-50 text-purple-700 ring-1 ring-purple-200 dark:bg-purple-900/20 dark:text-purple-200 dark:ring-purple-800/60',
            default =>
                'bg-neutral-100 text-neutral-700 ring-1 ring-neutral-200 dark:bg-neutral-700/60 dark:text-neutral-200 dark:ring-neutral-600/60',
        };
    }

    public function render()
    {
        $alumnos = null;
        $modalStats = $this->modalStatsVacias();
        $sinCounts = ['vacias' => 0, 'formato' => 0, 'duplicadas' => 0, 'todos' => 0];
        $licenciaturas = collect();
        $modalidades = collect();
        $generaciones = collect();
        $cuatrimestres = collect();
        $materias = collect();
        $profesores = collect();
        $cuatrimestresAcademicos = collect();
        $chartData = ['categories' => [], 'series' => []];
        $selectionCount = 0;

        if ($this->modalOpen) {
            $alumnos = $this->construirConsultaAlumnos(true)
                ->paginate($this->perPage, ['*'], 'matriculasPage');

            $modalStats = $this->obtenerEstadisticasModal();
            $sinCounts = $this->modalTipo === 'sin' ? $this->obtenerConteosSinMatricula() : $sinCounts;
            $licenciaturas = $this->opcionesLicenciaturas();
            $modalidades = $this->opcionesModalidades();
            $generaciones = $this->opcionesGeneraciones();
            $cuatrimestres = $this->opcionesCuatrimestres();
            $materias = $this->modalTipo === 'bajos' ? $this->opcionesMaterias() : collect();
            $profesores = $this->modalTipo === 'bajos' ? $this->opcionesProfesores() : collect();
            $cuatrimestresAcademicos = $this->modalTipo === 'bajos'
                ? $this->opcionesCuatrimestresAcademicos()
                : collect();
            $chartData = $this->obtenerDatosGrafica();
            $selectionCount = $this->conteoSeleccionados((int) $alumnos->total());
        }

        return view('livewire.header', compact(
            'alumnos',
            'modalStats',
            'sinCounts',
            'licenciaturas',
            'modalidades',
            'generaciones',
            'cuatrimestres',
            'materias',
            'profesores',
            'cuatrimestresAcademicos',
            'chartData',
            'selectionCount'
        ));
    }

    private function cargarDashboard(): void
    {
        $this->dashboard = Dashboard::query()->latest('id')->first();
    }

    private function cargarEstadisticas(): void
    {
        $base = Inscripcion::query();
        $this->aplicarEstadoGeneracion($base, 'activas');

        $this->total = (clone $base)->count();
        $this->conMatricula = (clone $base)->where(fn (Builder $query) => $this->aplicarMatriculaValida($query))->count();
        $this->matriculasVacias = (clone $base)->where(fn (Builder $query) => $this->aplicarMatriculaVacia($query))->count();
        $this->matriculasDuplicadas = (clone $base)->where(fn (Builder $query) => $this->aplicarMatriculaDuplicada($query))->count();
        $this->matriculasIncorrectas = (clone $base)->where(fn (Builder $query) => $this->aplicarMatriculaFormatoIncorrecto($query))->count();
        $this->sinMatricula = max(0, $this->total - $this->conMatricula);

        $this->bajos = (clone $base)
            ->whereHas('calificaciones', fn (Builder $query) => $this->aplicarRiesgoAcademico($query, false, 'todos'))
            ->count();

        $this->porcConMatricula = $this->porcentaje($this->conMatricula, $this->total);
        $this->porcSinMatricula = $this->porcentaje($this->sinMatricula, $this->total);
        $this->porcBajos = $this->porcentaje($this->bajos, $this->total);
    }

    private function construirConsultaAlumnos(bool $relaciones): Builder
    {
        $query = Inscripcion::query();
        $this->aplicarFiltrosComunes($query);
        $this->aplicarTipoModal($query);

        if ($relaciones) {
            $query->select('inscripciones.*')->selectSub(function ($subquery) {
                $subquery->from('inscripciones as coincidencias')
                    ->selectRaw('COUNT(*)')
                    ->whereNotNull('coincidencias.matricula')
                    ->whereRaw("TRIM(coincidencias.matricula) <> ''")
                    ->whereRaw('UPPER(TRIM(coincidencias.matricula)) = UPPER(TRIM(inscripciones.matricula))');
            }, 'matricula_coincidencias');

            $query->with([
                'licenciatura:id,nombre,nombre_corto,RVOE',
                'modalidad:id,nombre',
                'generacion:id,generacion,activa',
                'cuatrimestre:id,cuatrimestre,nombre_cuatrimestre',
            ]);

            if ($this->modalTipo === 'bajos') {
                $query->with([
                    'calificaciones' => function (Relation $calificaciones): void {
                        $this->aplicarRiesgoAcademico($calificaciones, true);
                        $calificaciones
                            ->with([
                                'asignacionMateria:id,materia_id,cuatrimestre_id,profesor_id',
                                'asignacionMateria.materia:id,nombre,clave',
                                'asignacionMateria.cuatrimestre:id,cuatrimestre,nombre_cuatrimestre',
                                'profesor:id,nombre,apellido_paterno,apellido_materno',
                            ])
                            ->orderBy('cuatrimestre_id')
                            ->orderBy('id');
                    },
                ]);
            }
        }

        return $query
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->orderBy('id');
    }

    private function aplicarFiltrosComunes(Builder $query): void
    {
        $this->aplicarEstadoGeneracion($query, $this->generacionEstado);

        $query
            ->when($this->licenciaturaId !== '', fn (Builder $q) => $q->where('inscripciones.licenciatura_id', $this->licenciaturaId))
            ->when($this->modalidadId !== '', fn (Builder $q) => $q->where('inscripciones.modalidad_id', $this->modalidadId))
            ->when($this->generacionId !== '', fn (Builder $q) => $q->where('inscripciones.generacion_id', $this->generacionId))
            ->when($this->cuatrimestreId !== '', fn (Builder $q) => $q->where('inscripciones.cuatrimestre_id', $this->cuatrimestreId))
            ->when($this->sexo !== '', fn (Builder $q) => $q->where('inscripciones.sexo', $this->sexo))
            ->when($this->residencia === 'local', fn (Builder $q) => $q->where('inscripciones.foraneo', 'false'))
            ->when($this->residencia === 'foraneo', fn (Builder $q) => $q->where('inscripciones.foraneo', 'true'))
            ->when($this->estadoAlumno === 'activo', fn (Builder $q) => $q->where('inscripciones.status', 'true'))
            ->when($this->estadoAlumno === 'baja', fn (Builder $q) => $q->where('inscripciones.status', 'false'))
            ->when($this->fechaDesde, fn (Builder $q) => $q->whereDate('inscripciones.created_at', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn (Builder $q) => $q->whereDate('inscripciones.created_at', '<=', $this->fechaHasta));

        $term = trim($this->search);

        if ($term !== '') {
            $like = '%' . preg_replace('/\s+/', '%', $term) . '%';

            $query->where(function (Builder $where) use ($like): void {
                $where
                    ->where('inscripciones.matricula', 'like', $like)
                    ->orWhere('inscripciones.CURP', 'like', $like)
                    ->orWhere('inscripciones.nombre', 'like', $like)
                    ->orWhere('inscripciones.apellido_paterno', 'like', $like)
                    ->orWhere('inscripciones.apellido_materno', 'like', $like)
                    ->orWhereRaw("CONCAT_WS(' ', inscripciones.nombre, inscripciones.apellido_paterno, inscripciones.apellido_materno) LIKE ?", [$like])
                    ->orWhereHas('licenciatura', fn (Builder $relacion) => $relacion->where('nombre', 'like', $like))
                    ->orWhereHas('modalidad', fn (Builder $relacion) => $relacion->where('nombre', 'like', $like))
                    ->orWhereHas('generacion', fn (Builder $relacion) => $relacion->where('generacion', 'like', $like))
                    ->orWhereHas('cuatrimestre', function (Builder $relacion) use ($like): void {
                        $relacion->where('nombre_cuatrimestre', 'like', $like)
                            ->orWhere('cuatrimestre', 'like', $like);
                    });
            });
        }
    }

    private function aplicarTipoModal(Builder $query): void
    {
        match ($this->modalTipo) {
            'con' => $query->where(fn (Builder $where) => $this->aplicarMatriculaValida($where)),
            'sin' => $query->where(fn (Builder $where) => $this->aplicarSinMatricula($where)),
            'bajos' => $query->whereHas('calificaciones', fn (Builder $calificaciones) => $this->aplicarRiesgoAcademico($calificaciones, true)),
            default => null,
        };
    }

    private function aplicarEstadoGeneracion(Builder $query, string $estado): void
    {
        if (! Schema::hasColumn('generaciones', 'activa') || $estado === 'todas') {
            return;
        }

        $query->whereHas('generacion', function (Builder $generacion) use ($estado): void {
            $generacion->where('activa', $estado === 'finalizadas' ? 'false' : 'true');
        });
    }

    private function aplicarMatriculaVacia(Builder $query): void
    {
        $query->where(function (Builder $where): void {
            $where->whereNull('inscripciones.matricula')
                ->orWhereRaw("TRIM(COALESCE(inscripciones.matricula, '')) = ''");
        });
    }

    private function aplicarMatriculaDuplicada(Builder $query): void
    {
        $query
            ->whereNotNull('inscripciones.matricula')
            ->whereRaw("TRIM(inscripciones.matricula) <> ''")
            ->whereExists(function ($duplicada): void {
                $duplicada
                    ->selectRaw('1')
                    ->from('inscripciones as matriculas_duplicadas')
                    ->whereColumn('matriculas_duplicadas.id', '<>', 'inscripciones.id')
                    ->whereNotNull('matriculas_duplicadas.matricula')
                    ->whereRaw("TRIM(matriculas_duplicadas.matricula) <> ''")
                    ->whereRaw(
                        'UPPER(TRIM(matriculas_duplicadas.matricula)) = UPPER(TRIM(inscripciones.matricula))'
                    );
            });
    }

    private function aplicarMatriculaValida(Builder $query): void
    {
        $query
            ->whereNotNull('inscripciones.matricula')
            ->whereRaw("TRIM(inscripciones.matricula) <> ''")
            ->whereRaw('UPPER(TRIM(inscripciones.matricula)) REGEXP ?', [MatriculaService::REGEX_SQL])
            ->whereNotExists(function ($duplicada): void {
                $duplicada
                    ->selectRaw('1')
                    ->from('inscripciones as matriculas_duplicadas')
                    ->whereColumn('matriculas_duplicadas.id', '<>', 'inscripciones.id')
                    ->whereNotNull('matriculas_duplicadas.matricula')
                    ->whereRaw("TRIM(matriculas_duplicadas.matricula) <> ''")
                    ->whereRaw(
                        'UPPER(TRIM(matriculas_duplicadas.matricula)) = UPPER(TRIM(inscripciones.matricula))'
                    );
            });
    }

    private function aplicarMatriculaFormatoIncorrecto(Builder $query): void
    {
        $query
            ->whereNotNull('inscripciones.matricula')
            ->whereRaw("TRIM(inscripciones.matricula) <> ''")
            ->whereRaw('UPPER(TRIM(inscripciones.matricula)) NOT REGEXP ?', [MatriculaService::REGEX_SQL])
            ->whereNotExists(function ($duplicada): void {
                $duplicada
                    ->selectRaw('1')
                    ->from('inscripciones as matriculas_duplicadas')
                    ->whereColumn('matriculas_duplicadas.id', '<>', 'inscripciones.id')
                    ->whereNotNull('matriculas_duplicadas.matricula')
                    ->whereRaw("TRIM(matriculas_duplicadas.matricula) <> ''")
                    ->whereRaw(
                        'UPPER(TRIM(matriculas_duplicadas.matricula)) = UPPER(TRIM(inscripciones.matricula))'
                    );
            });
    }

    private function aplicarSinMatricula(Builder $query): void
    {
        match ($this->sinCategoria) {
            'vacias' => $this->aplicarMatriculaVacia($query),
            'formato' => $this->aplicarMatriculaFormatoIncorrecto($query),
            'duplicadas' => $this->aplicarMatriculaDuplicada($query),
            default => $query->where(function (Builder $where): void {
                $where->where(fn (Builder $q) => $this->aplicarMatriculaVacia($q))
                    ->orWhere(fn (Builder $q) => $this->aplicarMatriculaFormatoIncorrecto($q))
                    ->orWhere(fn (Builder $q) => $this->aplicarMatriculaDuplicada($q));
            }),
        };
    }

    private function aplicarRiesgoAcademico(Builder|Relation $query, bool $conFiltros, ?string $tipo = null): void
    {
        $codigos = ['NP', 'N/P', 'N.P.', 'NA'];

        $tipo ??= $this->riesgoTipo;

        if ($tipo === 'np') {
            $query->whereIn(DB::raw('UPPER(TRIM(calificaciones.calificacion))'), $codigos);
        } elseif ($tipo === 'numerica') {
            $query->whereRaw("TRIM(calificaciones.calificacion) REGEXP '^[0-9]+(\\.[0-9]+)?$'")
                ->whereRaw('CAST(calificaciones.calificacion AS DECIMAL(5,2)) <= 6');
        } else {
            $query->where(function (Builder $where) use ($codigos): void {
                $where->whereIn(DB::raw('UPPER(TRIM(calificaciones.calificacion))'), $codigos)
                    ->orWhere(function (Builder $numero): void {
                        $numero->whereRaw("TRIM(calificaciones.calificacion) REGEXP '^[0-9]+(\\.[0-9]+)?$'")
                            ->whereRaw('CAST(calificaciones.calificacion AS DECIMAL(5,2)) <= 6');
                    });
            });
        }

        if (! $conFiltros) {
            return;
        }

        $query
            ->when($this->materiaId !== '', function (Builder $calificaciones): void {
                $calificaciones->whereHas('asignacionMateria', fn (Builder $asignacion) => $asignacion->where('materia_id', $this->materiaId));
            })
            ->when($this->profesorId !== '', fn (Builder $calificaciones) => $calificaciones->where('profesor_id', $this->profesorId))
            ->when($this->cuatrimestreAcademicoId !== '', function (Builder $calificaciones): void {
                $calificaciones->where(function (Builder $where): void {
                    $where->where('calificaciones.cuatrimestre_id', $this->cuatrimestreAcademicoId)
                        ->orWhereHas('asignacionMateria', fn (Builder $asignacion) => $asignacion->where('cuatrimestre_id', $this->cuatrimestreAcademicoId));
                });
            });
    }

    private function obtenerEstadisticasModal(): array
    {
        $query = $this->construirConsultaAlumnos(false)->reorder();

        $stats = $query->selectRaw("\n            COUNT(*) AS total,\n            SUM(CASE WHEN inscripciones.status = 'true' THEN 1 ELSE 0 END) AS activos,\n            SUM(CASE WHEN inscripciones.status = 'false' THEN 1 ELSE 0 END) AS bajas,\n            SUM(CASE WHEN inscripciones.foraneo = 'true' THEN 1 ELSE 0 END) AS foraneos,\n            SUM(CASE WHEN inscripciones.foraneo = 'false' THEN 1 ELSE 0 END) AS locales,\n            SUM(CASE WHEN inscripciones.sexo = 'H' THEN 1 ELSE 0 END) AS hombres,\n            SUM(CASE WHEN inscripciones.sexo = 'M' THEN 1 ELSE 0 END) AS mujeres\n        ")->first();

        return [
            'total' => (int) ($stats->total ?? 0),
            'activos' => (int) ($stats->activos ?? 0),
            'bajas' => (int) ($stats->bajas ?? 0),
            'foraneos' => (int) ($stats->foraneos ?? 0),
            'locales' => (int) ($stats->locales ?? 0),
            'hombres' => (int) ($stats->hombres ?? 0),
            'mujeres' => (int) ($stats->mujeres ?? 0),
        ];
    }

    private function obtenerConteosSinMatricula(): array
    {
        $base = Inscripcion::query();
        $this->aplicarFiltrosComunes($base);

        $vacias = (clone $base)->where(fn (Builder $q) => $this->aplicarMatriculaVacia($q))->count();
        $formato = (clone $base)->where(fn (Builder $q) => $this->aplicarMatriculaFormatoIncorrecto($q))->count();
        $duplicadas = (clone $base)->where(fn (Builder $q) => $this->aplicarMatriculaDuplicada($q))->count();

        return [
            'vacias' => $vacias,
            'formato' => $formato,
            'duplicadas' => $duplicadas,
            'todos' => $vacias + $formato + $duplicadas,
        ];
    }

    private function obtenerDatosGrafica(): array
    {
        $rows = $this->construirConsultaAlumnos(false)
            ->reorder()
            ->join('licenciaturas', 'licenciaturas.id', '=', 'inscripciones.licenciatura_id')
            ->selectRaw('licenciaturas.id, COALESCE(licenciaturas.nombre_corto, licenciaturas.nombre) AS etiqueta, COUNT(*) AS total')
            ->groupBy('licenciaturas.id', 'licenciaturas.nombre_corto', 'licenciaturas.nombre')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        return [
            'categories' => $rows->pluck('etiqueta')->values()->all(),
            'series' => $rows->pluck('total')->map(fn ($value) => (int) $value)->values()->all(),
        ];
    }

    private function opcionesLicenciaturas(): Collection
    {
        return Licenciatura::query()
            ->whereHas('inscripciones', fn (Builder $inscripciones) => $this->aplicarEstadoGeneracion($inscripciones, $this->generacionEstado))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'nombre_corto']);
    }

    private function opcionesModalidades(): Collection
    {
        return Modalidad::query()
            ->whereHas('inscripcion', function (Builder $inscripciones): void {
                $this->aplicarEstadoGeneracion($inscripciones, $this->generacionEstado);
                $inscripciones->when($this->licenciaturaId !== '', fn (Builder $q) => $q->where('licenciatura_id', $this->licenciaturaId));
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function opcionesGeneraciones(): Collection
    {
        return Generacion::query()
            ->when($this->generacionEstado !== 'todas' && Schema::hasColumn('generaciones', 'activa'), function (Builder $query): void {
                $query->where('activa', $this->generacionEstado === 'finalizadas' ? 'false' : 'true');
            })
            ->whereHas('inscripcion', function (Builder $inscripciones): void {
                $inscripciones
                    ->when($this->licenciaturaId !== '', fn (Builder $q) => $q->where('licenciatura_id', $this->licenciaturaId))
                    ->when($this->modalidadId !== '', fn (Builder $q) => $q->where('modalidad_id', $this->modalidadId));
            })
            ->orderByDesc('generacion')
            ->get(['id', 'generacion', 'activa']);
    }

    private function opcionesCuatrimestres(): Collection
    {
        return Cuatrimestre::query()
            ->whereHas('inscripciones', function (Builder $inscripciones): void {
                $this->aplicarEstadoGeneracion($inscripciones, $this->generacionEstado);
                $inscripciones
                    ->when($this->licenciaturaId !== '', fn (Builder $q) => $q->where('licenciatura_id', $this->licenciaturaId))
                    ->when($this->modalidadId !== '', fn (Builder $q) => $q->where('modalidad_id', $this->modalidadId))
                    ->when($this->generacionId !== '', fn (Builder $q) => $q->where('generacion_id', $this->generacionId));
            })
            ->orderBy('cuatrimestre')
            ->get(['id', 'cuatrimestre', 'nombre_cuatrimestre']);
    }

    private function opcionesMaterias(): Collection
    {
        return Materia::query()
            ->when($this->licenciaturaId !== '', fn (Builder $query) => $query->where('licenciatura_id', $this->licenciaturaId))
            ->whereHas('asignacionMaterias.calificaciones', fn (Builder $calificaciones) => $this->aplicarRiesgoAcademico($calificaciones, false))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'clave']);
    }

    private function opcionesProfesores(): Collection
    {
        return Profesor::query()
            ->whereHas('calificaciones', fn (Builder $calificaciones) => $this->aplicarRiesgoAcademico($calificaciones, false))
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']);
    }

    private function opcionesCuatrimestresAcademicos(): Collection
    {
        return Cuatrimestre::query()
            ->whereHas('calificaciones', fn (Builder $calificaciones) => $this->aplicarRiesgoAcademico($calificaciones, false))
            ->orderBy('cuatrimestre')
            ->get(['id', 'cuatrimestre', 'nombre_cuatrimestre']);
    }

    private function aplicarSeleccion(Builder $query): void
    {
        if ($this->selectAllFiltered) {
            if ($this->excludedIds !== []) {
                $query->whereNotIn('inscripciones.id', array_map('intval', $this->excludedIds));
            }

            return;
        }

        $ids = array_values(array_unique(array_map('intval', $this->selectedIds)));

        if ($ids === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('inscripciones.id', $ids);
    }

    private function resumenFiltros(): array
    {
        $filtros = [
            'Vista' => $this->tituloModal(),
            'Generaciones' => match ($this->generacionEstado) {
                'finalizadas' => 'Finalizadas',
                'todas' => 'Todas',
                default => 'Activas',
            },
        ];

        if ($this->search !== '') {
            $filtros['Búsqueda'] = $this->search;
        }
        if ($this->licenciaturaId !== '') {
            $filtros['Licenciatura'] = Licenciatura::query()->find($this->licenciaturaId)?->nombre ?? '—';
        }
        if ($this->modalidadId !== '') {
            $filtros['Modalidad'] = Modalidad::query()->find($this->modalidadId)?->nombre ?? '—';
        }
        if ($this->generacionId !== '') {
            $filtros['Generación'] = Generacion::query()->find($this->generacionId)?->generacion ?? '—';
        }
        if ($this->cuatrimestreId !== '') {
            $cuatrimestre = Cuatrimestre::query()->find($this->cuatrimestreId);
            $filtros['Cuatrimestre'] = $cuatrimestre?->nombre_cuatrimestre ?? $cuatrimestre?->cuatrimestre ?? '—';
        }
        if ($this->sexo !== '') {
            $filtros['Sexo'] = $this->sexo === 'M' ? 'Mujeres' : 'Hombres';
        }
        if ($this->residencia !== '') {
            $filtros['Procedencia'] = $this->residencia === 'foraneo' ? 'Foráneos' : 'Locales';
        }
        if ($this->estadoAlumno !== '') {
            $filtros['Estado'] = $this->estadoAlumno === 'activo' ? 'Activos' : 'Bajas';
        }
        if ($this->fechaDesde || $this->fechaHasta) {
            $filtros['Fecha'] = trim(($this->fechaDesde ?: 'Inicio') . ' a ' . ($this->fechaHasta ?: 'Hoy'));
        }

        return $filtros;
    }

    private function tituloModal(): string
    {
        return match ($this->modalTipo) {
            'sin' => 'Alumnos sin matrícula válida',
            'bajos' => 'Alumnos con riesgo académico',
            'todos' => 'Total de inscripciones',
            default => 'Alumnos con matrícula válida',
        };
    }

    private function autorizarAdministracion(): void
    {
        abort_unless(auth()->user()?->can('admin.administracion'), 403);
    }

    private function conteoSeleccionados(int $totalFiltrado): int
    {
        return $this->selectAllFiltered
            ? max(0, $totalFiltrado - count($this->excludedIds))
            : count($this->selectedIds);
    }

    private function resetSelection(): void
    {
        $this->selectedIds = [];
        $this->excludedIds = [];
        $this->selectAllFiltered = false;
    }

    private function toggleId(array $ids, int $id): array
    {
        $ids = array_map('intval', $ids);

        if (in_array($id, $ids, true)) {
            return array_values(array_diff($ids, [$id]));
        }

        $ids[] = $id;

        return array_values(array_unique($ids));
    }

    private function porcentaje(int $cantidad, int $total): int
    {
        return $total > 0 ? (int) round(($cantidad / $total) * 100) : 0;
    }

    private function modalStatsVacias(): array
    {
        return [
            'total' => 0,
            'activos' => 0,
            'bajas' => 0,
            'foraneos' => 0,
            'locales' => 0,
            'hombres' => 0,
            'mujeres' => 0,
        ];
    }
}
