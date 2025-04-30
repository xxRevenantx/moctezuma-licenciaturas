<?php

namespace App\Livewire\Admin\Licenciatura;

use App\Exports\LicenciaturaExport;
use App\Models\Licenciatura;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Termwind\Components\Li;


class MostrarLicenciaturas extends Component
{

    use WithPagination;

    public $search = '';




    public static function placeholder(){
        return view('placeholder');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }



    public function eliminarLicenciatura($id)
    {
        $licenciatura = Licenciatura::find($id);

        if ($licenciatura) {
            // Eliminar la imagen asociada si existe
            $imagePath = public_path('storage/licenciaturas/' . $licenciatura->imagen);
            if ($licenciatura->imagen && file_exists($imagePath)) {
            unlink($imagePath);
            }

            $licenciatura->delete();

            $this->dispatch('swal', [
            'title' => '¡Licenciatura eliminada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
            ]);
        }
    }

    public function exportarLicenciaturas()
    {

        $licenciaturasFiltradas = Licenciatura::where('nombre', 'like', '%' . $this->search . '%')
            ->orWhere('nombre_corto', 'like', '%' . $this->search . '%')
            ->orWhere('RVOE', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->get(['id','nombre', 'nombre_corto', 'RVOE']); // columnas deseadas

        return Excel::download(new LicenciaturaExport($licenciaturasFiltradas), 'licenciaturas_filtradas.xlsx');
    }


    #[On('refreshLicenciaturas')]
    public function render()
    {
        $licenciaturas = Licenciatura::where('nombre', 'like', '%' . $this->search . '%')
            ->orWhere('nombre_corto', 'like', '%' . $this->search . '%')
            ->orWhere('RVOE', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('livewire.admin.licenciatura.mostrar-licenciaturas', compact('licenciaturas'));
    }
}
