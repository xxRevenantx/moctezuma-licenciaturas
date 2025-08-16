<?php

namespace App\Livewire\Admin\Periodo;

use App\Exports\PeriodoExport;
use App\Models\Cuatrimestre;
use App\Models\Generacion;
use App\Models\Mes;
use App\Models\Periodo;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class MostrarPeriodos extends Component
{
    use WithFileUploads;
    use WithPagination;

    // Si usas Tailwind para la paginación
    protected string $paginationTheme = 'tailwind';

    public $search = '';

    public $filtrar_cuatrimestre = null;
    public $filtrar_generacion   = null;
    public $filtrar_mes          = null;
    public $filtar_inicio_periodo = null; // YYYY-MM-DD
    public $filtar_termino_periodo = null; // YYYY-MM-DD

    public $perPage = 20;

    // (Opcional) conserva filtros en la URL.
    protected $queryString = [
        'search'                => ['except' => ''],
        'filtrar_cuatrimestre'  => ['except' => ''],
        'filtrar_generacion'    => ['except' => ''],
        'filtrar_mes'           => ['except' => ''],
        'filtar_inicio_periodo' => ['except' => ''],
        'filtar_termino_periodo'=> ['except' => ''],
        'page'                  => ['except' => 1],
    ];

    /**
     * Query base reutilizable (listado y export).
     */
    private function buildQuery()
    {
        $q = Periodo::query()->with(['cuatrimestre','generacion','mes']);

        // Filtros
        $q->when($this->filtrar_cuatrimestre, fn($qq, $v) => $qq->where('cuatrimestre_id', $v));
        $q->when($this->filtrar_generacion,   fn($qq, $v) => $qq->where('generacion_id',   $v));
        $q->when($this->filtrar_mes,          fn($qq, $v) => $qq->where('mes_id',          $v));

        $q->when($this->filtar_inicio_periodo, fn($qq, $v) => $qq->whereDate('inicio_periodo',  $v));
        $q->when($this->filtar_termino_periodo, fn($qq, $v) => $qq->whereDate('termino_periodo', $v));

        // Búsqueda (AGRUPADA para que no se “coma” los filtros)
        $q->when($this->search, function ($qq, $v) {
            $qq->where(function ($sub) use ($v) {
                $sub->where('ciclo_escolar', 'like', "%{$v}%")
                    ->orWhereHas('cuatrimestre', fn($s) => $s->where('cuatrimestre', 'like', "%{$v}%"))
                    ->orWhereHas('generacion',  fn($s) => $s->where('generacion',   'like', "%{$v}%"))
                    ->orWhereHas('mes',         fn($s) => $s->where('meses',        'like', "%{$v}%"));
            });
        });

        // Solo generaciones activas (si es booleano, usa true/1; si es string, deja "true")
        $q->whereHas('generacion', fn($qq) => $qq->where('activa', true));

        return $q->orderBy('order', 'asc')->orderBy('cuatrimestre_id', 'asc');
    }

    /**
     * Computado para la vista.
     */
    public function getPeriodosProperty()
    {
        return $this->buildQuery()->paginate($this->perPage);
    }

    // ---- Reset de página en cambios ----
    public function updatedSearch()               { $this->resetPage(); }
    public function updatedFiltrarCuatrimestre()  { $this->resetPage(); }
    public function updatedFiltrarGeneracion()    { $this->resetPage(); }
    public function updatedFiltrarMes()           { $this->resetPage(); }
    public function updatedFiltarInicioPeriodo()  { $this->resetPage(); }
    public function updatedFiltarTerminoPeriodo() { $this->resetPage(); }

    // (Compat) si prefieres un genérico:
    // public function updating($name, $value) { $this->resetPage(); }

    // --------- Acciones ----------
    public function limpiarFiltros()
    {
        $this->reset([
            'search',
            'filtrar_cuatrimestre',
            'filtrar_generacion',
            'filtrar_mes',
            'filtar_inicio_periodo',
            'filtar_termino_periodo',
        ]);
        $this->resetPage();
    }

    public function ordenarId()
    {
        // Si solo quieres “limpiar” para ver todo
        $this->limpiarFiltros();
    }

    public function eliminarPeriodo($id)
    {
        $periodo = Periodo::find($id);

        if ($periodo) {
            $periodo->delete();

            $this->dispatch('swal', [
                'title'    => '¡Periodo eliminado correctamente!',
                'icon'     => 'success',
                'position' => 'top-end',
            ]);
            // Tras eliminar, recargar página correcta si la actual quedó vacía
            if ($this->periodos->isEmpty() && $this->page > 1) {
                $this->previousPage();
            }
        }
    }

    public function exportarPeriodos()
    {
        $periodosFiltrados = $this->buildQuery()->get();
        return Excel::download(new PeriodoExport($periodosFiltrados), 'periodos_filtrados.xlsx');
    }

    #[On('refreshPeriodos')]
    public function render()
    {
        $cuatrimestres = Cuatrimestre::all();
        $generaciones  = Generacion::where('activa', true)->get();
        $meses         = Mes::all();

        return view('livewire.admin.periodo.mostrar-periodos', [
            'periodos'      => $this->periodos,
            'cuatrimestres' => $cuatrimestres,
            'generaciones'  => $generaciones,
            'meses'         => $meses,
        ]);
    }
}
