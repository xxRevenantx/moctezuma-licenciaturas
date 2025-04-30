<?php

namespace App\Livewire\Admin\Generacion;

use App\Exports\GeneracionExport;
use App\Models\Generacion;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

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
            ->orWhere(function ($query) {
            $query->where('activa', "true")->whereRaw('? like ?', ['si', '%' . $this->search . '%']);
            })
            ->orWhere(function ($query) {
            $query->where('activa', "false")->whereRaw('? like ?', ['no', '%' . $this->search . '%']);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }



    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function eliminarGeneracion($id)
    {
        $generacion = Generacion::find($id);

        if ($generacion) {
            $generacion->delete();

            $this->dispatch('swal', [
            'title' => 'Generación eliminada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
            ]);
        }
    }

    public function exportarGeneraciones()
    {

        $generacionesFiltradas = Generacion::where('generacion', 'like', '%' . $this->search . '%')
            ->orWhere('activa', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->get();

    return Excel::download(new GeneracionExport($generacionesFiltradas), 'generaciones_filtradas.xlsx');
    }


    #[On('refreshGeneracion')]
    public function render()
    {
        return view('livewire.admin.generacion.mostrar-generaciones', [
            'generaciones' => $this->generaciones,
        ]);
    }
}
