<?php

namespace App\Livewire\Admin\Accion;

use Livewire\Component;

class MostrarAcciones extends Component
{
    public function render()
    {
        $acciones = \App\Models\Accion::orderBy('order')->paginate(10);
        return view('livewire.admin.accion.mostrar-acciones', compact('acciones'));
    }
}
