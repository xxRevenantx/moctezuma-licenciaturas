<?php

namespace App\Livewire\Admin\Cuatrimestre;

use App\Models\Cuatrimestre;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MostrarCuatrimestres extends Component
{

    use WithPagination;

    public $search = '';


    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function eliminarCuatrimestre($id)
    {
        $cuatrimestre = Cuatrimestre::find($id);

        if ($cuatrimestre) {
            $cuatrimestre->delete();

            $this->dispatch('swal', [
            'title' => '¡Cuatrimestre eliminado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
            ]);
        }

        $this->dispatch('refreshCuatrimestre');
    }

    #[On('refreshCuatrimestre')]
    public function render()
    {
        $cuatrimestres = Cuatrimestre::where('cuatrimestre', 'like', '%' . $this->search . '%')
            ->orWhereHas('mes', function ($query) {
                 $query->where('meses', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id')
            ->with('mes')
            ->paginate(10);
        return view('livewire.admin.cuatrimestre.mostrar-cuatrimestres', compact('cuatrimestres'));
    }
}
