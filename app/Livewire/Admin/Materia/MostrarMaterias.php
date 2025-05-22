<?php

namespace App\Livewire\Admin\Materia;

use App\Exports\MateriaExport;
use App\Imports\MateriaImport;
use App\Models\Cuatrimestre;
use App\Models\Licenciatura;
use App\Models\Materia;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class MostrarMaterias extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $search = '';
    public $erroresImportacion;

    public $archivo;

    public $filtrar_licenciatura;
    public $filtrar_cuatrimestre;
    public $filtrar_calificable;


    public function updatingSearch()
    {
        $this->resetPage();
    }

     public function getMateriasProperty()
    {
        $query = Materia::with(['cuatrimestre', 'licenciatura']);


        if ($this->filtrar_licenciatura) {
            $query->where('licenciatura_id', $this->filtrar_licenciatura);
        }


        if ($this->filtrar_cuatrimestre) {
            $query->where('cuatrimestre_id', $this->filtrar_cuatrimestre);
        }


        if ($this->filtrar_calificable == 'true') {
            $query->where('calificable', "true");
        } elseif ($this->filtrar_calificable == 'false') {
            $query->where('calificable', "false");
        }




        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . trim($this->search) . '%')
                    ->orWhere('slug', 'like', '%' . trim($this->search) . '%')
                    ->orWhere('clave', 'like', '%' . trim($this->search) . '%');
            });

        }

        return $query
            ->orderBy('licenciatura_id', 'asc')
            ->orderBy('cuatrimestre_id', 'asc')
            ->orderBy('clave', 'asc')
            ->paginate(20);
    }


    public function importarMaterias()
    {

        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new MateriaImport;

        try {

       Excel::import($import, $this->archivo->getRealPath());


            if ($import->failures()->isNotEmpty()) {

                $this->erroresImportacion = $import->failures()->toArray();

                dd($this->erroresImportacion);

                $this->dispatch('swal', [
                    'title' => 'Errores en la importación. Verifica el archivo.',
                    'icon' => 'error',
                    'position' => 'top-end',
                ]);

            } else {
                $this->reset(['archivo', 'erroresImportacion']);
                $this->dispatch('swal', [
                    'title' => '¡Materias importadas correctamente!',
                    'icon' => 'success',
                    'position' => 'top-end',
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'title' => 'Error al importar el archivo',
                'icon' => 'error',
                'position' => 'top-end',
            ]);
        }


    }

    public function limpiarFiltros()
    {
        $this->reset([
            'search',
            'filtrar_licenciatura',
            'filtrar_cuatrimestre',
            'filtrar_calificable',
        ]);
    }

    // Eliminar materia
    public function eliminarMateria($id){

        $materia = Materia::findOrFail($id);
        if ($materia) {
            $materia->delete();
            $this->dispatch('swal', [
                'title' => '¡Materia eliminada correctamente!',
                'icon' => 'success',
                'position' => 'top-end',
            ]);
        } else {
            $this->dispatch('swal', [
                'title' => '¡Error al eliminar la materia!',
                'icon' => 'error',
                'position' => 'top-end',
            ]);
        }

        $this->dispatch('refreshMaterias');

    }

    //Exportar materias
    public function exportarMaterias()
    {
    $materias_filtrada = Materia::with(['cuatrimestre', 'licenciatura'])
        ->when($this->filtrar_licenciatura, function ($query) {
            $query->where('licenciatura_id', $this->filtrar_licenciatura);
        })
        ->when($this->filtrar_cuatrimestre, function ($query) {
            $query->where('cuatrimestre_id', $this->filtrar_cuatrimestre);
        })
        ->when($this->filtrar_calificable, function ($query) {
            $query->where('calificable', $this->filtrar_calificable);
        })
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . trim($this->search) . '%')
                    ->orWhere('slug', 'like', '%' . trim($this->search) . '%')
                    ->orWhere('clave', 'like', '%' . trim($this->search) . '%');
            });
        })
        ->orderBy('licenciatura_id', 'asc')
        ->orderBy('cuatrimestre_id', 'asc')
        ->orderBy('clave', 'asc')
        ->get();
        return Excel::download(new MateriaExport($materias_filtrada), 'materias.xlsx');
    }


     #[On('refreshMaterias')]
    public function render()
    {
        return view('livewire.admin.materia.mostrar-materias', [
            'materias' => $this->materias,
            'licenciaturas' => Licenciatura::all(),
            'cuatrimestres' => Cuatrimestre::all(),
        ]);
    }
}
