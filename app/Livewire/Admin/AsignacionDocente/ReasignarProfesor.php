<?php

namespace App\Livewire\Admin\AsignacionDocente;

use App\Models\AsignacionMateria;
use App\Models\Cuatrimestre;
use App\Models\Generacion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Profesor;
use App\Services\ReasignacionDocenteService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ReasignarProfesor extends Component
{
    use WithPagination;

    #[Locked]
    public ?int $licenciaturaActual = null;
    public bool $abierto = false;
    public bool $confirmacion = false;
    public string $origen = '';
    public string $destino = '';
    public string $alcance = 'actual';
    public array $licenciaturas = [];
    public array $modalidades = [];
    public array $cuatrimestres = [];
    public array $generaciones = [];
    public string $busqueda = '';
    public bool $incluirHorarios = true;
    public array $seleccionadas = [];
    #[Locked]
    public array $revision = [];
    public bool $aceptaVinculos = false;
    public bool $aceptaTraslapes = false;
    #[Locked]
    public string $mensaje = '';

    public function mount(?int $licenciaturaActual = null, ?int $modalidadActual = null): void
    {
        Gate::authorize('admin.administracion');
        $this->licenciaturaActual = $licenciaturaActual;
        $this->alcance = $licenciaturaActual ? 'actual' : 'todas';
        $this->modalidades = $modalidadActual ? [(string) $modalidadActual] : [];
    }

    public function updated(string $propiedad): void
    {
        $raiz = explode('.', $propiedad)[0];
        if (in_array($raiz, ['origen', 'alcance', 'licenciaturas', 'modalidades', 'cuatrimestres', 'generaciones', 'busqueda', 'incluirHorarios'], true)) {
            $this->seleccionadas = [];
            $this->resetPage('reasignacionPage');
        }
        if (! in_array($raiz, ['aceptaVinculos', 'aceptaTraslapes', 'confirmacion'], true)) {
            $this->revision = [];
            $this->aceptaVinculos = $this->aceptaTraslapes = false;
            $this->resetValidation();
            $this->mensaje = '';
        }
    }

    private function consulta()
    {
        $q = AsignacionMateria::query()->where('profesor_id', (int) $this->origen);
        if ($this->alcance === 'actual') {
            $q->where('licenciatura_id', $this->licenciaturaActual ?? 0);
        } elseif ($this->alcance === 'seleccionadas') {
            $q->whereIn('licenciatura_id', $this->licenciaturas);
        } elseif ($this->alcance !== 'todas') {
            $q->whereRaw('1 = 0');
        }
        foreach (['licenciaturas' => 'licenciatura_id', 'modalidades' => 'modalidad_id', 'cuatrimestres' => 'cuatrimestre_id'] as $campo => $columna) {
            if ($campo !== 'licenciaturas' && $this->$campo) $q->whereIn($columna, $this->$campo);
        }
        if (! $this->incluirHorarios) $q->whereDoesntHave('horarios');
        if ($this->generaciones) {
            // Filtro de localización: las asignaciones no pertenecen a una sola generación.
            $q->where(function ($g) {
                $g->whereHas('horarios', fn ($h) => $h->whereIn('generacion_id', $this->generaciones))
                    ->orWhereHas('calificaciones', fn ($c) => $c->whereIn('generacion_id', $this->generaciones))
                    ->orWhereExists(function ($a) {
                        $a->selectRaw('1')->from('asignar_generaciones as ag')
                            ->whereColumn('ag.licenciatura_id', 'asignacion_materias.licenciatura_id')
                            ->whereColumn('ag.modalidad_id', 'asignacion_materias.modalidad_id')
                            ->whereIn('ag.generacion_id', $this->generaciones);
                    });
            });
        }
        if (trim($this->busqueda) !== '') {
            $q->whereHas('materia', fn ($m) => $m->where(function ($s) {
                $s->where('nombre', 'like', '%'.trim($this->busqueda).'%')
                    ->orWhere('clave', 'like', '%'.trim($this->busqueda).'%');
            }));
        }
        return $q;
    }

    public function seleccionarTodas(): void
    {
        Gate::authorize('admin.administracion');
        $this->seleccionadas = $this->consulta()->orderBy('id')->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->revision = [];
    }

    public function limpiarSeleccion(): void
    {
        $this->seleccionadas = [];
        $this->revision = [];
    }

    public function revisar(): void
    {
        Gate::authorize('admin.administracion');
        $this->resetValidation();
        $this->revision = [];
        $this->aceptaVinculos = $this->aceptaTraslapes = false;
        $this->validate([
            'origen' => 'required|integer|min:1', 'destino' => 'required|integer|min:1|different:origen',
            'seleccionadas' => 'required|array|min:1', 'seleccionadas.*' => 'required|integer|min:1|distinct',
        ], [
            'destino.different' => 'El profesor de reemplazo debe ser diferente.',
            'seleccionadas.required' => 'Selecciona al menos una materia.',
            'origen.required' => 'Selecciona al profesor actual.',
            'destino.required' => 'Selecciona al profesor de reemplazo.',
        ]);
        $ids = $this->consulta()->whereIn('id', $this->seleccionadas)->pluck('id')->all();
        if (count($ids) !== count($this->seleccionadas)) {
            $this->addError('reasignacion', 'La selección cambió o contiene materias fuera de los filtros. Selecciona nuevamente.');
            return;
        }
        try {
            $this->revision = app(ReasignacionDocenteService::class)->preparar($ids, (int) $this->origen, (int) $this->destino);
            $this->confirmacion = true;
        } catch (\RuntimeException $e) {
            $this->addError('reasignacion', $e->getMessage());
        }
    }

    public function cancelarRevision(): void
    {
        $this->confirmacion = false;
        $this->revision = [];
        $this->aceptaVinculos = $this->aceptaTraslapes = false;
        $this->resetValidation();
    }

    public function confirmar(): void
    {
        Gate::authorize('admin.administracion');
        if (! $this->revision) return;
        try {
            $cantidad = app(ReasignacionDocenteService::class)->ejecutar($this->revision, $this->aceptaVinculos, $this->aceptaTraslapes);
            $this->cancelarRevision();
            $this->seleccionadas = [];
            $this->mensaje = "Se reasignaron {$cantidad} materias. Las calificaciones se conservaron.";
            $this->resetPage('reasignacionPage');
            $this->dispatch('reasignacion-masiva-completada');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('reasignacion', 'No se guardaron cambios. Revisa el registro de errores y vuelve a intentarlo.');
        }
    }

    public function render()
    {
        Gate::authorize('admin.administracion');
        return view('livewire.admin.asignacion-docente.reasignar-profesor', [
            'filas' => $this->abierto ? $this->consulta()->with(['materia', 'licenciatura', 'modalidad', 'cuatrimestre'])
                ->withCount(['horarios', 'calificaciones'])->orderBy('licenciatura_id')->orderBy('cuatrimestre_id')->orderBy('id')
                ->paginate(20, ['*'], 'reasignacionPage') : null,
            'profesores' => $this->abierto ? Profesor::orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombre')->get() : collect(),
            'opcionesLicenciaturas' => $this->abierto ? Licenciatura::orderBy('nombre')->get() : collect(),
            'opcionesModalidades' => $this->abierto ? Modalidad::orderBy('nombre')->get() : collect(),
            'opcionesCuatrimestres' => $this->abierto ? Cuatrimestre::orderBy('cuatrimestre')->get() : collect(),
            'opcionesGeneraciones' => $this->abierto ? Generacion::orderByDesc('id')->get() : collect(),
        ]);
    }
}
