<?php

namespace App\Livewire\Admin\AsignarGeneracion;

use App\Exports\AsignarGeneracionExport;
use App\Imports\AsignarGeneracionImport;
use App\Models\AsignarGeneracion;
use App\Models\Generacion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class MostrarGeneraciones extends Component
{
    use WithPagination;
    public $search = '';
    public $archivo;

    public $filtrar_licenciatura = '';
    public $filtrar_generacion = '';
    public $filtrar_modalidad = '';
    public $filtrar_activa = '';


    public $sortField = 'order';
    public $sortDirection = 'asc';

    public $erroresImportacion;


    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getAsignacionesProperty()
{
    $query = AsignarGeneracion::with(['licenciatura', 'modalidad', 'generacion']);

    if ($this->filtrar_licenciatura) {
        $query->where('licenciatura_id', $this->filtrar_licenciatura);
        $this->search= '';
    }

    if ($this->filtrar_generacion) {
        $query->where('generacion_id', $this->filtrar_generacion);
         $this->search= '';
    }

    if ($this->filtrar_modalidad) {
        $query->where('modalidad_id', $this->filtrar_modalidad);
         $this->search= '';
    }

    if ($this->filtrar_activa !== '') {
        $query->whereHas('generacion', function ($q) {
            $q->where('activa', $this->filtrar_activa === 'true' ? "true" : "false");
        });
         $this->search= '';
    }

    if ($this->search) {
        $query->where(function ($q) {
            $q->whereHas('licenciatura', function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->orWhereHas('modalidad', function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->orWhereHas('generacion', function ($query) {
                $query->where('generacion', 'like', '%' . $this->search . '%');
            });
        });


    }

    return $query
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate(10);
}

        // Este método se ejecuta cuando se cambia el valor del campo de búsqueda
        public function updatedSearch()
        {
            $this->resetFilters();
            $this->resetPage();
        }

        // Estos métodos se ejecutan cuando cambian los filtros
        public function updatedFiltrarLicenciatura()
        {
            $this->search = '';
            $this->resetPage();
        }

        public function updatedFiltrarGeneracion()
        {
            $this->search = '';
            $this->resetPage();
        }

        public function updatedFiltrarModalidad()
        {
            $this->search = '';
            $this->resetPage();
        }

        public function updatedFiltrarActiva()
        {
            $this->search = '';
            $this->resetPage();
        }

        // Este método reinicia todos los filtros
        protected function resetFilters()
        {
            $this->filtrar_licenciatura = '';
            $this->filtrar_generacion = '';
            $this->filtrar_modalidad = '';
            $this->filtrar_activa = '';
        }


    public function updating($property)
    {
        if (in_array($property, ['filtrar_licenciatura', 'filtrar_generacion', 'filtrar_modalidad', 'filtrar_activa', 'search'])) {
            $this->resetPage();
        }
    }


    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function eliminarAsignacion($id)
    {
        $asignacion = AsignarGeneracion::find($id);

        if ($asignacion) {
            $asignacion->delete();

            $this->dispatch('swal', [
            'title' => 'Asignación eliminada correctamente',
            'icon' => 'success',
            'position' => 'top-end',
            ]);
        }
    }


    // exportar a excel
        public function exportarAsignacion(){
            $asignaciones = AsignarGeneracion::with(['licenciatura', 'modalidad', 'generacion'])
                ->when($this->filtrar_licenciatura, function ($query) {
                    $query->where('licenciatura_id', $this->filtrar_licenciatura);
                })
                ->when($this->filtrar_generacion, function ($query) {
                    $query->where('generacion_id', $this->filtrar_generacion);
                })
                ->when($this->filtrar_modalidad, function ($query) {
                    $query->where('modalidad_id', $this->filtrar_modalidad);
                })
                ->when($this->filtrar_activa !== '', function ($query) {
                    $query->whereHas('generacion', function ($q) {
                        $q->where('activa', $this->filtrar_activa === 'true' ? "true" : "false");
                    });
                })
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->whereHas('licenciatura', function ($query) {
                            $query->where('nombre', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('modalidad', function ($query) {
                            $query->where('nombre', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('generacion', function ($query) {
                            $query->where('generacion', 'like', '%' . $this->search . '%');
                        });
                    });
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->get();

            return Excel::download(new AsignarGeneracionExport($asignaciones), 'generaciones_asignadas.xlsx');
        }

    // Importar desde excel
        // public function importarAsignaciones(){
        //     $this->validate([
        //         'archivo' => 'required|file|mimes:xlsx,xls,csv',
        //     ]);

        //     $import = new AsignarGeneracionImport;

        //     try {
        //         Excel::import($import, $this->archivo->getRealPath());

        //         if ($import->failures()->isNotEmpty()) {

        //             $this->erroresImportacion = $import->failures()->toArray();

        //             $this->dispatch('swal', [
        //                 'title' => 'Errores en la importación. Verifica el archivo.',
        //                 'icon' => 'error',
        //                 'position' => 'top-end',
        //             ]);

        //         } else {
        //             $this->reset(['archivo', 'erroresImportacion']);
        //             $this->dispatch('swal', [
        //                 'title' => 'Generaciones importadas correctamente!',
        //                 'icon' => 'success',
        //                 'position' => 'top-end',
        //             ]);
        //         }
        //     } catch (\Exception $e) {
        //         $this->dispatch('swal', [
        //             'title' => 'Error al importar el archivo',
        //             'icon' => 'error',
        //             'position' => 'top-end',
        //         ]);
        //     }

        //     $this->reset();


        // }




    #[On("refreshAsignacion")]
    public function render()
    {
        $licenciaturas = Licenciatura::all();
        $generaciones = Generacion::all();
        $modalidades = Modalidad::all();
        return view('livewire.admin.asignar-generacion.mostrar-generaciones',
        ['asignaciones' => $this->asignaciones,
        'licenciaturas' => $licenciaturas,
        'modalidades' => $modalidades,
        'generaciones' => $generaciones

    ]);


    }
}
