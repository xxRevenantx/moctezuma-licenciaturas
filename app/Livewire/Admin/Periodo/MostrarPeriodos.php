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

    public $search = '';

    public $archivo;


    public $filtrar_cuatrimestre;
    public $filtrar_generacion;
    public $filtrar_mes;
    public $filtar_inicio_periodo;
    public $filtar_termino_periodo;



    public function getPeriodosProperty()
    {
        $query = Periodo::with(['cuatrimestre', 'generacion', 'mes']);

        if ($this->filtrar_cuatrimestre) {
            $query->where('cuatrimestre_id', $this->filtrar_cuatrimestre);
        }

        if ($this->filtrar_generacion) {
            $query->where('generacion_id', $this->filtrar_generacion);
        }

        if ($this->filtrar_mes) {
            $query->where('mes_id', $this->filtrar_mes);
        }

        if ($this->filtar_inicio_periodo) {
            $query->where('inicio_periodo', 'like', '%' . $this->filtar_inicio_periodo . '%');
        }

        if ($this->filtar_termino_periodo) {
            $query->where('termino_periodo', 'like', '%' . $this->filtar_termino_periodo . '%');
        }




        if ($this->search) {
            $query->where('ciclo_escolar', 'like', '%' . $this->search . '%')
                ->orWhereHas('cuatrimestre', function ($query) {
                    $query->where('cuatrimestre', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('generacion', function ($query) {
                    $query->where('generacion', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('mes', function ($query) {
                    $query->where('meses', 'like', '%' . $this->search . '%');
                });
        }

        // Ensure that the 'status' column in the 'generacion' table is true
        $query->whereHas('generacion', function ($query) {
            $query->where('activa', "true");
        });

        return $query
            ->orderBy('order', 'asc')
            ->orderBy('cuatrimestre_id', 'asc')
            ->paginate(20);
    }


      // Este método se ejecuta cuando se cambia el valor del campo de búsqueda
      public function updatedSearch()
      {
        //   $this->resetFilters();
          $this->resetPage();
      }

      // Estos métodos se ejecutan cuando cambian los filtros
        public function updatedFiltrarCuatrimestre()
        {

            $this->resetPage();
        }
        public function updatedFiltrarGeneracion()
        {

            $this->resetPage();
        }
        public function updatedFiltrarMes()
        {

            $this->resetPage();
        }



      // Este método reinicia todos los filtros
      protected function resetFilters()
      {
            $this->filtrar_cuatrimestre = "";
            $this->filtrar_generacion = "";
            $this->filtrar_mes = "";

      }

      public function limpiarFiltros(){
        $this->resetFilters();
        $this->search = '';
        $this->filtar_inicio_periodo = '';
        $this->filtar_termino_periodo = '';
        $this->resetPage();
      }

      // ORDENAR POR ID

      public function ordenarId()
      {
        $this->resetPage();
        $this->filtrar_cuatrimestre = null;
        $this->filtrar_generacion = null;
        $this->filtrar_mes = null;
        $this->filtar_inicio_periodo = null;
        $this->filtar_termino_periodo = null;
      }


      // Este método se ejecuta cuando se cambia el valor de un filtro
      public function updating($property)
    {
        if (in_array($property, ['filtrar_cuatrimestre', 'filtrar_generacion', 'filtrar_mes', 'search'])) {
            $this->resetPage();
        }
    }




    public function eliminarPeriodo($id)
    {
        $periodo = Periodo::find($id);

        if ($periodo) {
            $periodo->delete();

            $this->dispatch('swal', [
            'title' => '¡Periodo eliminado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
            ]);
        }
    }

    public function exportarPeriodos()
    {

        $periodosFiltrados = Periodo::with(['cuatrimestre', 'generacion', 'mes'])
            ->where(function ($query) {
                if ($this->filtrar_cuatrimestre) {
                    $query->where('cuatrimestre_id', $this->filtrar_cuatrimestre);
                }

                if ($this->filtrar_generacion) {
                    $query->where('generacion_id', $this->filtrar_generacion);
                }

                if ($this->filtrar_mes) {
                    $query->where('mes_id', $this->filtrar_mes);
                }

                if ($this->filtar_inicio_periodo) {
                    $query->where('inicio_periodo', 'like', '%' . $this->filtar_inicio_periodo . '%');
                }

                if ($this->filtar_termino_periodo) {
                    $query->where('termino_periodo', 'like', '%' . $this->filtar_termino_periodo . '%');
                }
            })
            ->whereHas('generacion', function ($query) {
                $query->where('activa', "true");
            })
            ->get();

    return Excel::download(new PeriodoExport($periodosFiltrados), 'periodos_filtrados.xlsx');
    }


    #[On('refreshPeriodos')]
    public function render()
    {
        $cuatrimestres = Cuatrimestre::all();
        $generaciones = Generacion::all();
        $meses = Mes::all();

        return view('livewire.admin.periodo.mostrar-periodos',[
            'periodos' => $this->periodos,
            'cuatrimestres' => $cuatrimestres,
            'generaciones' => $generaciones,
            'meses' => $meses,
        ]);
    }
}
