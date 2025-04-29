<?php

namespace App\Livewire\Admin\Directivo;

use App\Models\Directivo;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MostrarDirectivos extends Component
{

    use WithPagination;

    public $search = '';

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
