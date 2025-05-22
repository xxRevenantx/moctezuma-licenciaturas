<?php

namespace App\Livewire\Admin\Materia;

use App\Imports\MateriaImport;
use App\Models\Materia;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class MostrarMaterias extends Component
{
    use WithFileUploads;

    public $search = '';
    public $erroresImportacion;

    public $archivo;

     public function getMateriasProperty()
    {
        $query = Materia::with(['cuatrimestre', 'licenciatura']);

        // if ($this->filtrar_cuatrimestre) {
        //     $query->where('cuatrimestre_id', $this->filtrar_cuatrimestre);
        // }



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



        return $query
            ->orderBy('order', 'asc')
            ->paginate(20);
    }


    public function importarMaterias()
    {
        dd($this->archivo);
        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv',
        ]);



        $import = new MateriaImport;

        try {


       Excel::import($import, $this->archivo->getRealPath());



            if ($import->failures()->isNotEmpty()) {

                $this->erroresImportacion = $import->failures()->toArray();

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

    public function render()
    {
        return view('livewire.admin.materia.mostrar-materias', [
            'materias' => $this->materias
        ]);
    }
}
