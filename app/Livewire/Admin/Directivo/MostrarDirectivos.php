<?php

namespace App\Livewire\Admin\Directivo;

use App\Exports\DirectivoExport;
use App\Imports\DirectivoImport;
use App\Models\Directivo;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class MostrarDirectivos extends Component
{

    use WithPagination;

    public $search = '';

    use WithFileUploads;

    public $archivo;

    public $erroresImportacion;


    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function eliminarDirectivo($id)
    {
        $directivo = Directivo::find($id);

        if ($directivo) {
            $directivo->delete();

            $this->dispatch('swal', [
            'title' => 'Directivo eliminado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
            ]);
        }
    }

    public function exportarDirectivos()
    {

        $directivosFiltrados = Directivo::where('nombre', 'like', '%' . $this->search . '%')
            ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
            ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
            ->orWhere('telefono', 'like', '%' . $this->search . '%')
            ->orWhere('correo', 'like', '%' . $this->search . '%')
            ->orWhere('cargo', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->get();

        return Excel::download(new DirectivoExport($directivosFiltrados), 'directivos_filtradas.xlsx');
    }

    public function importarDirectivos()
    {
        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new DirectivoImport;

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
                    'title' => '¡Directivos importados correctamente!',
                    'icon' => 'success',
                    'position' => 'top-end',
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'title' => 'Error al importar el archivo: ' . $e->getMessage(),
                'icon' => 'error',
                'position' => 'top-end',
            ]);
        }

        $this->reset();



    }


    #[On('refreshDirectivos')]
    public function render()
    {
        $directivos = Directivo::where('nombre', 'like', '%' . $this->search . '%')
            ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
            ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
            ->orWhere('telefono', 'like', '%' . $this->search . '%')
            ->orWhere('correo', 'like', '%' . $this->search . '%')
            ->orWhere('cargo', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('livewire.admin.directivo.mostrar-directivos', compact('directivos'));
    }
}
