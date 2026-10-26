<?php

namespace App\Livewire\Admin\Profesor;

use App\Exports\ProfesorExport;
use App\Models\Profesor;
use App\Services\Profesores\ProfesorManagementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class MostrarProfesores extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    #[Url(keep: true, except: '')]
    public string $filtrar_status = ''; // Compatibilidad: filtro histórico por estado de ACCESO del usuario.

    #[Url(keep: true, except: '')]
    public string $filtrar_disponibilidad = '';

    #[Url(keep: true, except: '')]
    public string $search = '';

    /** @var int[] */
    public array $selected = [];
    public bool $selectAll = false;

    public function mount(ProfesorManagementService $service): void
    {
        $service->autorizar();
    }

    public function updated($name, $value): void
    {
        if (in_array($name, ['search', 'filtrar_status', 'filtrar_disponibilidad'], true)) {
            $this->resetPage('prof_page');
            $this->limpiarSeleccion();
        }
    }

    public function updatingPage($page, $pageName = 'page'): void
    {
        $this->limpiarSeleccion();
    }

    public function updatedSelectAll($value): void
    {
        // Solo página visible; nunca todos los IDs del resultado completo sin una segunda confirmación.
        $this->selected = $value ? $this->profesores->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all() : [];
    }

    public function updatedSelected(): void
    {
        $this->selected = array_values(array_unique(array_map('intval', $this->selected)));
        $idsPagina = $this->profesores->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->selectAll = count($idsPagina) > 0 && ! array_diff($idsPagina, $this->selected);
        unset($this->impactoSeleccion);
    }

    #[Computed]
    public function profesores()
    {
        return $this->filtrados()->with('user')->withCount('asignacionMaterias')
            ->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombre')
            ->paginate(15, ['*'], 'prof_page');
    }

    #[Computed]
    public function impactoSeleccion(): int
    {
        if (! $this->selected) {
            return 0;
        }
        return (int) Profesor::query()->whereIn('id', $this->selected)
            ->withCount('asignacionMaterias')->get()->sum('asignacion_materias_count');
    }

    #[Computed]
    public function indicadores(): array
    {
        $base = $this->filtrados();
        return [
            'total' => (clone $base)->count(),
            'disponibles' => (clone $base)->where('status', 'true')->count(),
            'con_carga' => (clone $base)->whereHas('asignacionMaterias')->count(),
            'acceso_pendiente' => (clone $base)->whereHas('user', fn (Builder $q) => $q->where('status', 'false'))->count(),
        ];
    }

    protected function filtrados(): Builder
    {
        $query = Profesor::query();
        if (in_array($this->filtrar_status, ['Activo', 'Inactivo'], true)) {
            $query->whereHas('user', fn (Builder $q) => $q->where('status', $this->filtrar_status === 'Activo' ? 'true' : 'false'));
        }
        if (in_array($this->filtrar_disponibilidad, ['Disponible', 'No disponible'], true)) {
            $query->where('status', $this->filtrar_disponibilidad === 'Disponible' ? 'true' : 'false');
        }
        $buscar = trim($this->search);
        if ($buscar !== '') {
            $buscar = str_replace(['%', '_'], ['\\%', '\\_'], $buscar);
            $query->where(function (Builder $q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                    ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%")
                    ->orWhere('perfil', 'like', "%{$buscar}%")
                    ->orWhereHas('user', fn (Builder $u) => $u->where('email', 'like', "%{$buscar}%")
                        ->orWhere('username', 'like', "%{$buscar}%")
                        ->orWhere('CURP', 'like', "%{$buscar}%"));
            });
        }
        return $query;
    }

    /** La selección nunca permite operar sobre IDs fuera de los filtros en curso. */
    private function seleccionValidada(): array
    {
        $ids = array_values(array_unique(array_map('intval', $this->selected)));
        if (! $ids || count($ids) > 100 || min($ids) <= 0) {
            throw ValidationException::withMessages(['selected' => 'Selecciona entre 1 y 100 profesores.']);
        }
        $idsEnFiltro = $this->filtrados()->whereIn('profesores.id', $ids)->pluck('profesores.id')->map(fn ($id) => (int) $id)->all();
        if (count($idsEnFiltro) !== count($ids)) {
            throw ValidationException::withMessages(['selected' => 'La selección ya no coincide con los filtros actuales.']);
        }
        return $ids;
    }

    public function cambiarDisponibilidad(bool $disponible, ProfesorManagementService $service): void
    {
        $cantidad = $service->cambiarDisponibilidad($this->seleccionValidada(), $disponible);
        $this->limpiarSeleccion();
        unset($this->profesores, $this->indicadores);
        $this->dispatch('swal', [
            'title' => "{$cantidad} profesor(es) " . ($disponible ? 'disponibles' : 'marcados como no disponibles') . '. Se conservaron sus asignaciones y cuentas.',
            'icon' => 'success', 'position' => 'top-end',
        ]);
    }

    // Compatibilidad con eventos y nombres usados por la versión previa.
    public function activarprofesoresSeleccionados(ProfesorManagementService $service): void
    {
        $this->cambiarDisponibilidad(true, $service);
    }

    public function inactivarprofesoresSeleccionados(ProfesorManagementService $service): void
    {
        $this->cambiarDisponibilidad(false, $service);
    }

    public function exportarProfesores(ProfesorManagementService $service)
    {
        $service->autorizar();
        $query = $this->filtrados()->with('user')->withCount('asignacionMaterias')->orderBy('apellido_paterno');
        if ($this->selected) {
            $query->whereIn('profesores.id', $this->seleccionValidada());
        }
        return Excel::download(new ProfesorExport($query->get()), 'profesores_filtrados.xlsx');
    }

    private function limpiarSeleccion(): void
    {
        $this->selected = [];
        $this->selectAll = false;
        unset($this->impactoSeleccion);
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['filtrar_status', 'filtrar_disponibilidad', 'search']);
        $this->limpiarSeleccion();
        $this->resetPage('prof_page');
    }

    #[On('refreshProfesor')]
    public function render()
    {
        return view('livewire.admin.profesor.mostrar-profesores', [
            'profesores' => $this->profesores,
            'indicadores' => $this->indicadores,
        ]);
    }
}
