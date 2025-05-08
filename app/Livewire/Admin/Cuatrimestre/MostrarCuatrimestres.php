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

    public $selected = [];
    public $selectAll = false;

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = Cuatrimestre::pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function eliminarCuatrimestreSeleccionados()
    {
        if (count($this->selected) > 0) {
            Cuatrimestre::whereIn('id', $this->selected)->delete();

            $this->dispatch('swal', [
                'title' => '¡Cuatrimestres eliminados correctamente!',
                'icon' => 'success',
                'position' => 'top-end',
            ]);

            $this->selected = []; // Reset selected items
            $this->selectAll = false; // Reset select all checkbox
        } else {
            $this->dispatch('swal', [
                'title' => '¡No se han seleccionado cuatrimestres!',
                'icon' => 'warning',
                'position' => 'top-end',
            ]);
        }

        $this->dispatch('refreshCuatrimestre');
    }


    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function eliminarCuatrimestre($id)
    {
        $this->selected = []; // Reset selected items
        $this->selectAll = false; // Reset select all checkbox


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
