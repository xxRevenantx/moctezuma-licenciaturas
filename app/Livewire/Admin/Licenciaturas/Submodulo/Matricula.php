<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Exports\MatriculaExport;
use App\Models\AsignarGeneracion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\MovimientoAcademico;
use App\Models\Periodo;
use App\Services\AcademicMovementService;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class Matricula extends Component
{
    use WithPagination;

    public $modalidad;
    public $licenciatura;
    public $submodulo;
    public $generaciones;
    public $cuatrimestres;

    public string $search = '';
    public array $selected = [];
    public $filtrar_generacion = null;
    public $filtrar_foraneo = null;
    public int $perPage = 25;

    // Movimiento académico seguro: modalidad / cuatrimestre.
    public bool $movimientoOpen = false;
    public string $movimientoTipo = AcademicMovementService::TIPO_MODALIDAD;
    public array $movimientoAlumnoIds = [];
    public $movimientoModalidadDestinoId = null;
    public $movimientoCuatrimestreDestinoId = null;
    public string $movimientoMotivo = '';
    public ?string $movimientoObservaciones = null;
    public array $movimientoAnalisis = [];
    public int $movimientoPaso = 1;
    public bool $movimientoConfirmado = false;

    public function mount($licenciatura, $modalidad, $submodulo): void
    {
        $this->licenciatura = Licenciatura::query()->where('slug', $licenciatura)->firstOrFail();
        $this->modalidad = Modalidad::query()->where('slug', $modalidad)->firstOrFail();
        $this->submodulo = $submodulo;

        $this->generaciones = AsignarGeneracion::query()
            ->with('generacion')
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->whereHas('generacion', fn ($query) => $query->where('activa', 'true'))
            ->orderByDesc('generacion_id')
            ->get();

        $this->cuatrimestres = collect();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->limpiarSeleccionados();
    }

    public function updatedFiltrarForaneo(): void
    {
        $this->resetPage();
        $this->limpiarSeleccionados();
    }

    public function updatedPerPage($value): void
    {
        $permitidos = [25, 50, 100];
        $this->perPage = in_array((int) $value, $permitidos, true) ? (int) $value : 25;
        $this->resetPage();
        $this->limpiarSeleccionados();
    }

    public function updatedFiltrarGeneracion(): void
    {
        $this->resetPage();
        $this->limpiarSeleccionados();
        $this->actualizarCuatrimestres();
        $this->dispatch('cerrarModalPdf');
    }

    public function getMatriculaProperty()
    {
        if (! $this->filtrar_generacion) {
            return Inscripcion::query()->whereRaw('1 = 0')->paginate($this->perPage);
        }

        return $this->queryMatricula()
            ->with(['generacion', 'cuatrimestre', 'licenciatura', 'modalidad'])
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->paginate($this->perPage);
    }

    public function getResumenProperty(): array
    {
        if (! $this->filtrar_generacion) {
            return [
                'total' => 0,
                'mujeres' => 0,
                'hombres' => 0,
                'locales' => 0,
                'foraneos' => 0,
            ];
        }

        $resumen = $this->queryMatricula()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN sexo = 'M' THEN 1 ELSE 0 END) as mujeres")
            ->selectRaw("SUM(CASE WHEN sexo = 'H' THEN 1 ELSE 0 END) as hombres")
            ->selectRaw("SUM(CASE WHEN foraneo = 'false' THEN 1 ELSE 0 END) as locales")
            ->selectRaw("SUM(CASE WHEN foraneo = 'true' THEN 1 ELSE 0 END) as foraneos")
            ->first();

        return [
            'total' => (int) ($resumen?->total ?? 0),
            'mujeres' => (int) ($resumen?->mujeres ?? 0),
            'hombres' => (int) ($resumen?->hombres ?? 0),
            'locales' => (int) ($resumen?->locales ?? 0),
            'foraneos' => (int) ($resumen?->foraneos ?? 0),
        ];
    }

    public function getMovimientoAlumnosProperty()
    {
        if ($this->movimientoAlumnoIds === []) {
            return collect();
        }

        return Inscripcion::query()
            ->with(['modalidad:id,nombre', 'cuatrimestre:id,nombre_cuatrimestre'])
            ->whereIn('id', $this->movimientoAlumnoIds)
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();
    }

    public function getMovimientosRecientesProperty()
    {
        if (! $this->filtrar_generacion || ! Schema::hasTable('movimientos_academicos')) {
            return collect();
        }

        return MovimientoAcademico::query()
            ->with([
                'modalidadOrigen:id,nombre',
                'modalidadDestino:id,nombre',
                'cuatrimestreOrigen:id,nombre_cuatrimestre',
                'cuatrimestreDestino:id,nombre_cuatrimestre',
                'ejecutor:id,username',
            ])
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where(function ($query) {
                $query->where('modalidad_origen_id', $this->modalidad->id)
                    ->orWhere('modalidad_destino_id', $this->modalidad->id);
            })
            ->latest('id')
            ->limit(15)
            ->get();
    }

    public function seleccionarPagina(): void
    {
        if (! $this->filtrar_generacion) {
            return;
        }

        $ids = $this->matricula->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->selected = array_values(array_unique(array_merge($this->normalizarSeleccion(), $ids)));
    }

    public function seleccionarTodosFiltrados(): void
    {
        if (! $this->filtrar_generacion) {
            return;
        }

        $this->selected = $this->queryMatricula()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function limpiarSeleccionados(): void
    {
        $this->selected = [];
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtrar_generacion = null;
        $this->filtrar_foraneo = null;
        $this->cuatrimestres = collect();
        $this->limpiarSeleccionados();
        $this->resetPage();
        $this->dispatch('cerrarModalPdf');
    }

    #[On('solicitarMovimientoAcademico')]
    public function solicitarMovimientoAcademico(int $estudianteId): void
    {
        $this->abrirMovimientoAcademico($estudianteId, AcademicMovementService::TIPO_MODALIDAD);
    }

    public function abrirMovimientoAcademico(?int $estudianteId = null, string $tipo = 'modalidad'): void
    {
        if (! Schema::hasTable('movimientos_academicos')) {
            $this->dispatch('swal', [
                'title' => 'Ejecuta primero la migración de movimientos académicos.',
                'icon' => 'warning',
                'position' => 'top-end',
            ]);
            return;
        }

        if (! in_array($tipo, [AcademicMovementService::TIPO_MODALIDAD, AcademicMovementService::TIPO_CUATRIMESTRE], true)) {
            $tipo = AcademicMovementService::TIPO_MODALIDAD;
        }

        $ids = $estudianteId ? [(int) $estudianteId] : $this->normalizarSeleccion();

        if ($ids === []) {
            $this->dispatch('swal', [
                'title' => 'Selecciona al menos un estudiante.',
                'icon' => 'warning',
                'position' => 'top-end',
            ]);
            return;
        }

        $validos = Inscripcion::query()
            ->whereIn('id', $ids)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->when($this->filtrar_generacion, fn ($query) => $query->where('generacion_id', $this->filtrar_generacion))
            ->where('status', 'true')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (count($validos) !== count(array_unique($ids))) {
            $this->dispatch('swal', [
                'title' => 'La selección contiene alumnos fuera del contexto actual. Actualiza la lista e inténtalo de nuevo.',
                'icon' => 'warning',
                'position' => 'top-end',
            ]);
            return;
        }

        $this->resetMovimiento();
        $this->movimientoOpen = true;
        $this->movimientoTipo = $tipo;
        $this->movimientoAlumnoIds = $validos;

        if ($tipo === AcademicMovementService::TIPO_MODALIDAD) {
            $destinos = Modalidad::query()->where('id', '!=', $this->modalidad->id)->pluck('id');
            if ($destinos->count() === 1) {
                $this->movimientoModalidadDestinoId = (int) $destinos->first();
            }
        }
    }

    public function revisarImpacto(AcademicMovementService $service): void
    {
        if (! $this->movimientoIdsSiguenEnContexto()) {
            $this->dispatch('swal', [
                'title' => 'La selección cambió o ya no pertenece al contexto visible. Cierra el movimiento, actualiza la matrícula e inténtalo nuevamente.',
                'icon' => 'warning',
                'position' => 'top-end',
            ]);
            return;
        }

        $reglas = [
            'movimientoMotivo' => 'required|in:solicitud_alumno,ajuste_administrativo,cambio_horario,otro',
            'movimientoObservaciones' => 'nullable|max:1000|required_if:movimientoMotivo,otro',
        ];

        if ($this->movimientoTipo === AcademicMovementService::TIPO_MODALIDAD) {
            $reglas['movimientoModalidadDestinoId'] = 'required|integer|exists:modalidades,id';
        } else {
            $reglas['movimientoCuatrimestreDestinoId'] = 'required|integer|exists:cuatrimestres,id';
        }

        $this->validate($reglas, [
            'movimientoMotivo.required' => 'Selecciona el motivo del movimiento.',
            'movimientoMotivo.in' => 'El motivo seleccionado no es válido.',
            'movimientoObservaciones.required_if' => 'Describe el motivo cuando seleccionas "Otro".',
            'movimientoModalidadDestinoId.required' => 'Selecciona la modalidad destino.',
            'movimientoCuatrimestreDestinoId.required' => 'Selecciona el cuatrimestre destino.',
        ]);

        if ($this->movimientoTipo === AcademicMovementService::TIPO_MODALIDAD) {
            $this->movimientoAnalisis = $service->analizarCambioModalidad(
                $this->movimientoAlumnoIds,
                (int) $this->movimientoModalidadDestinoId
            );
        } else {
            $this->movimientoAnalisis = $service->analizarCambioCuatrimestre(
                $this->movimientoAlumnoIds,
                (int) $this->movimientoCuatrimestreDestinoId
            );
        }

        $this->movimientoPaso = 2;
    }

    public function irConfirmacionMovimiento(): void
    {
        if (! ($this->movimientoAnalisis['valido'] ?? false)) {
            $this->dispatch('swal', [
                'title' => 'El movimiento tiene bloqueos que deben resolverse antes de continuar.',
                'icon' => 'warning',
                'position' => 'top-end',
            ]);
            return;
        }

        $this->movimientoConfirmado = false;
        $this->movimientoPaso = 3;
    }

    public function volverPasoMovimiento(): void
    {
        $this->movimientoPaso = max(1, $this->movimientoPaso - 1);
        if ($this->movimientoPaso === 1) {
            $this->movimientoAnalisis = [];
        }
    }

    public function ejecutarMovimiento(AcademicMovementService $service): void
    {
        if (! $this->movimientoIdsSiguenEnContexto()) {
            $this->dispatch('swal', [
                'title' => 'La selección ya no coincide con la licenciatura, modalidad y generación visibles. No se aplicó ningún cambio.',
                'icon' => 'warning',
                'position' => 'top-end',
            ]);
            return;
        }

        if (! $this->movimientoConfirmado) {
            $this->addError('movimientoConfirmado', 'Confirma que revisaste el impacto antes de ejecutar el movimiento.');
            return;
        }

        try {
            if ($this->movimientoTipo === AcademicMovementService::TIPO_MODALIDAD) {
                $resultado = $service->cambiarModalidad(
                    $this->movimientoAlumnoIds,
                    (int) $this->movimientoModalidadDestinoId,
                    $this->movimientoMotivo,
                    $this->movimientoObservaciones
                );
            } else {
                $resultado = $service->cambiarCuatrimestre(
                    $this->movimientoAlumnoIds,
                    (int) $this->movimientoCuatrimestreDestinoId,
                    $this->movimientoMotivo,
                    $this->movimientoObservaciones
                );
            }

            $this->cerrarMovimiento();
            $this->limpiarSeleccionados();
            $this->resetPage();
            $this->actualizarCuatrimestres();
            $this->dispatch('refreshHeader');
            $this->dispatch('refreshNavbar');

            $mensaje = $resultado['lote'].' · '.$resultado['total_alumnos'].' estudiante(s)';
            if (($resultado['calificaciones_trasladadas'] ?? 0) > 0) {
                $mensaje .= ' · '.$resultado['calificaciones_trasladadas'].' calificaciones conservadas';
            }

            $this->dispatch('swal', [
                'title' => 'Movimiento académico aplicado: '.$mensaje,
                'icon' => 'success',
                'position' => 'top-end',
            ]);
        } catch (DomainException $e) {
            $this->dispatch('swal', [
                'title' => $e->getMessage(),
                'icon' => 'warning',
                'position' => 'top-end',
            ]);
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('swal', [
                'title' => 'No fue posible completar el movimiento. No se aplicaron cambios parciales.',
                'icon' => 'error',
                'position' => 'top-end',
            ]);
        }
    }

    public function cerrarMovimiento(): void
    {
        $this->resetMovimiento();
        $this->movimientoOpen = false;
        $this->resetValidation();
    }

    public function revertirMovimiento(int $movimientoId, AcademicMovementService $service): void
    {
        try {
            $movimiento = $service->revertir($movimientoId);
            $this->limpiarSeleccionados();
            $this->resetPage();
            $this->dispatch('refreshHeader');
            $this->dispatch('refreshNavbar');
            $this->dispatch('swal', [
                'title' => 'Movimiento individual del lote '.$movimiento->lote.' revertido correctamente.',
                'icon' => 'success',
                'position' => 'top-end',
            ]);
        } catch (DomainException $e) {
            $this->dispatch('swal', [
                'title' => $e->getMessage(),
                'icon' => 'warning',
                'position' => 'top-end',
            ]);
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('swal', [
                'title' => 'No fue posible revertir el movimiento de forma segura.',
                'icon' => 'error',
                'position' => 'top-end',
            ]);
        }
    }

    public function darBajaEstudiante(int $id): void
    {
        $alumno = Inscripcion::query()
            ->whereKey($id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('status', 'true')
            ->first();

        if (! $alumno) {
            $this->dispatch('swal', [
                'title' => 'El alumno ya no está activo en este contexto.',
                'icon' => 'warning',
                'position' => 'top-end',
            ]);
            return;
        }

        $alumno->forceFill([
            'status' => 'false',
            'fecha_baja' => now(),
        ])->save();

        $this->limpiarSeleccionados();
        $this->resetPage();
        $this->dispatch('refreshNavbar');
        $this->dispatch('swal', [
            'title' => 'Baja académica registrada. No se eliminaron calificaciones ni expediente.',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }

    public function exportarMatricula()
    {
        $query = $this->queryMatricula()
            ->with(['user', 'licenciatura', 'generacion', 'cuatrimestre', 'modalidad']);

        $seleccion = $this->normalizarSeleccion();
        if ($seleccion !== []) {
            $query->whereIn('id', $seleccion);
        }

        $alumnosFiltrados = $query
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        return Excel::download(new MatriculaExport($alumnosFiltrados), 'matricula.xlsx');
    }

    public function exportarMatriculaPDF()
    {
        $query = $this->queryMatricula()
            ->with(['user', 'licenciatura', 'generacion', 'cuatrimestre', 'modalidad']);

        $seleccion = $this->normalizarSeleccion();
        if ($seleccion !== []) {
            $query->whereIn('id', $seleccion);
        }

        $alumnosFiltrados = $query
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        $alumnosSanitizados = $alumnosFiltrados->map(function ($alumno) {
            $array = $alumno->toArray();
            array_walk_recursive($array, function (&$value) {
                if (is_string($value)) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
            });
            return $array;
        });

        $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.matriculaPDF', [
            'alumnos' => $alumnosSanitizados,
        ])->setPaper('letter', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Matrícula.pdf');
    }

    #[On('refreshMatricula')]
    public function render()
    {
        $modalidadesDestino = Modalidad::query()
            ->where('id', '!=', $this->modalidad->id)
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.licenciaturas.submodulo.matricula', [
            'matricula' => $this->matricula,
            'resumen' => $this->resumen,
            'modalidadesDestino' => $modalidadesDestino,
            'movimientoAlumnos' => $this->movimientoAlumnos,
            'movimientosRecientes' => $this->movimientosRecientes,
        ]);
    }

    private function queryMatricula(): Builder
    {
        return Inscripcion::query()
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('status', 'true')
            ->when($this->filtrar_foraneo !== null && $this->filtrar_foraneo !== '', function ($query) {
                $query->where('foraneo', $this->filtrar_foraneo);
            })
            ->when(trim($this->search) !== '', function ($query) {
                $search = trim($this->search);
                $query->where(function ($subquery) use ($search) {
                    $subquery->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('apellido_paterno', 'like', '%'.$search.'%')
                        ->orWhere('apellido_materno', 'like', '%'.$search.'%')
                        ->orWhere('matricula', 'like', '%'.$search.'%')
                        ->orWhere('folio', 'like', '%'.$search.'%')
                        ->orWhere('CURP', 'like', '%'.$search.'%');
                });
            });
    }

    private function actualizarCuatrimestres(): void
    {
        if (! $this->filtrar_generacion) {
            $this->cuatrimestres = collect();
            return;
        }

        $this->cuatrimestres = Periodo::query()
            ->with('cuatrimestre')
            ->where('generacion_id', $this->filtrar_generacion)
            ->orderBy('order')
            ->get()
            ->unique('cuatrimestre_id')
            ->values();
    }

    private function movimientoIdsSiguenEnContexto(): bool
    {
        $ids = $this->normalizarIdsMovimiento();

        if ($ids === [] || ! $this->filtrar_generacion) {
            return false;
        }

        $validos = Inscripcion::query()
            ->whereIn('id', $ids)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('status', 'true')
            ->count();

        return $validos === count($ids);
    }

    private function normalizarIdsMovimiento(): array
    {
        return collect($this->movimientoAlumnoIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizarSeleccion(): array
    {
        return collect($this->selected)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function resetMovimiento(): void
    {
        $this->movimientoTipo = AcademicMovementService::TIPO_MODALIDAD;
        $this->movimientoAlumnoIds = [];
        $this->movimientoModalidadDestinoId = null;
        $this->movimientoCuatrimestreDestinoId = null;
        $this->movimientoMotivo = '';
        $this->movimientoObservaciones = null;
        $this->movimientoAnalisis = [];
        $this->movimientoPaso = 1;
        $this->movimientoConfirmado = false;
    }
}
