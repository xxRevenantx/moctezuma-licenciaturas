<?php

namespace App\Livewire\Admin\Estado;

use App\Models\Estado;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MostrarEstados extends Component
{

    use WithPagination;

    public $search = '';


    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function eliminarEstado($id)
    {
        $estado = Estado::find($id);

        if ($estado) {
            $estado->delete();

            $this->dispatch('swal', [
            'title' => 'Estado eliminado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
            ]);
        }

        $this->dispatch('refreshEstados');
    }


    #[On('refreshEstados')]
    public function render()
    {
        $estados = Estado::where('nombre', 'like', '%' . $this->search . '%')
            ->orderBy('nombre','asc')
            ->paginate(10);
        return view('livewire.admin.estado.mostrar-estados', compact('estados'));

    }
}
