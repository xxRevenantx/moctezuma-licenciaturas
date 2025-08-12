<?php

namespace App\Livewire\Admin\Grupo;

use App\Exports\GrupoExport;
use App\Models\Grupo;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class MostrarGrupos extends Component
{
    use WithPagination;

    public $search = '';

    /**
     * Builder base reutilizable para lista y exportación.
     */
    protected function baseQuery()
    {
        return Grupo::with(['licenciatura', 'cuatrimestre'])
            ->where(function ($query) {
                $query->where('grupo', 'like', '%' . $this->search . '%')
                    ->orWhereHas('licenciatura', function ($q) {
                        $q->where('nombre', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('cuatrimestre', function ($q) {
                        $q->where('cuatrimestre', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderByLicenciatura('desc') // usa el scope del modelo, descendente
            ->orderBy('cuatrimestre_id'); // orden terciario descendente
    }

    public function getGruposProperty()
    {
        // Sube el tamaño de página para minimizar cortes entre licenciaturas
        return $this->baseQuery()->paginate(20);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function eliminarGrupo($id)
    {
        $grupo = Grupo::find($id);

        if ($grupo) {
            $grupo->delete();

            $this->dispatch('swal', [
                'title' => '¡Grupo eliminado correctamente!',
                'icon'  => 'success',
                'position' => 'top-end',
            ]);
        }
    }

    public function exportarGrupos()
    {
        $gruposFiltrados = $this->baseQuery()->get();

        return Excel::download(new GrupoExport($gruposFiltrados), 'grupos_filtrados.xlsx');
    }

    #[On('refreshGrupo')]
    public function render()
    {
        return view('livewire.admin.grupo.mostrar-grupos', [
            'grupos' => $this->grupos,
        ]);
    }
}
