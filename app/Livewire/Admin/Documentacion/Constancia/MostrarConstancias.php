<?php

namespace App\Livewire\Admin\Documentacion\Constancia;

use App\Models\Constancia;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MostrarConstancias extends Component
{

    use WithPagination;

    public $search = '';

     #[On('refreshConstancias')]
    public function render()
    {
            $constancias = Constancia::where('fecha_expedicion', 'like', '%' . $this->search . '%')
            ->orWhereHas('alumno', function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%');
            })
            ->with('alumno')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('livewire.admin.documentacion.constancia.mostrar-constancias', compact('constancias'));
    }
}
