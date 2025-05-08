<?php

namespace App\Livewire\Admin\Ciudad;

use App\Models\Ciudad;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MostrarCiudades extends Component
{

    use WithPagination;

    public $search = '';


    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function eliminarCiudad($id)
    {
        $ciudad = Ciudad::find($id);

        if ($ciudad) {
            $ciudad->delete();

            $this->dispatch('swal', [
            'title' => '¡Ciudad eliminada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
            ]);
        }

        $this->dispatch('refreshCiudades');
    }




    #[On('refreshCiudades')]
    public function render()
    {
        $ciudades = Ciudad::where('nombre', 'like', '%' . $this->search . '%')
            ->orderBy('nombre','asc')
            ->paginate(10);
        return view('livewire.admin.ciudad.mostrar-ciudades', compact('ciudades'));

    }
}
