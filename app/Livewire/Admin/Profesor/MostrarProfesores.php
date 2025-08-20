<?php

namespace App\Livewire\Admin\Profesor;

use App\Exports\ProfesorExport;
use App\Models\Profesor;
use Illuminate\Database\Eloquent\Builder;
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
    public string $filtrar_status = '';

    #[Url(keep: true, except: '')]
    public string $search = '';

    /** @var int[] */
    public array $selected = [];
    public bool $selectAll = false;

    /* -------- eventos de actualización -------- */
    public function updated($name, $value): void
    {
        if (in_array($name, ['search', 'filtrar_status'], true)) {
            $this->resetPage('prof_page');
            $this->selectAll = false;
            $this->selected = [];
        }
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $q = Profesor::query();
            $this->aplicarFiltros($q);
            $this->selected = $q->pluck('id')->all();
        } else {
            $this->selected = [];
        }
    }

    /* -------- lista computada + paginada -------- */
    #[Computed]
    public function profesores()
    {
        $q = Profesor::query()->orderBy('apellido_paterno');
        $this->aplicarFiltros($q);

        // nombre de página propio para evitar conflictos
        return $q->paginate(15, ['*'], 'prof_page');
    }

    /* -------- acciones -------- */
    public function exportarProfesores()
    {
        $q = Profesor::query();
        $this->aplicarFiltros($q);

        if ($this->selected) {
            $q->whereIn('id', $this->selected);
        }

        return Excel::download(
            new ProfesorExport($q->orderBy('apellido_paterno')->get()),
            'profesores_filtrados.xlsx'
        );
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['filtrar_status', 'search', 'selectAll', 'selected']);
        $this->resetPage('prof_page');
    }

    /* -------- filtros reutilizables -------- */
    protected function aplicarFiltros(Builder $q): void
    {
        // status
        if ($this->filtrar_status !== '') {
            $q->whereHas('user', fn (Builder $u) =>
                $u->where('status', $this->filtrar_status === 'Activo' ? 'true' : 'false')
            );
        }

        // texto libre
        if ($this->search !== '') {
            $buscar = trim($this->search);
            $buscarLower = mb_strtolower($buscar);

            $q->where(function (Builder $w) use ($buscar, $buscarLower) {
                $w->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                  ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%")
                  ->orWhere('perfil', 'like', "%{$buscar}%")
                  ->orWhereHas('user', function (Builder $u) use ($buscar, $buscarLower) {
                      $u->where('email', 'like', "%{$buscar}%")
                        ->orWhere('CURP', 'like', "%{$buscar}%");

                      // permite escribir "activo" / "inactivo"
                      if (str_contains($buscarLower, 'activo')) {
                          $u->orWhere('status', 'true');
                      }
                      if (str_contains($buscarLower, 'inactivo')) {
                          $u->orWhere('status', 'false');
                      }
                  });
            });
        }
    }

    #[On('refreshProfesor')]
    public function render()
    {
        return view('livewire.admin.profesor.mostrar-profesores', [
            'profesores' => $this->profesores,
        ]);
    }
}
