<?php

namespace App\Livewire\Admin\Generacion;

use App\Models\Generacion;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MostrarGeneraciones extends Component
{
   use WithPagination;

    public $search = '';

    public $sortField = 'id';
    public $sortDirection = 'asc';

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getGeneracionesProperty()
    {
        return Generacion::where('generacion', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }



    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function eliminarGeneracion($id)
    {
        $directivo = Generacion::find($id);

        if ($directivo) {
            $directivo->delete();

            $this->dispatch('swal', [
            'title' => 'Generación eliminada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
            ]);
        }
    }


    #[On('refreshGeneracion')]
    public function render()
    {
        return view('livewire.admin.generacion.mostrar-generaciones', [
            'generaciones' => $this->generaciones,
        ]);
    }
}
