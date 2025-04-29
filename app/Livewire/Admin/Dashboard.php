<?php

namespace App\Livewire\Admin;

use App\Models\Dashboard as ModelsDashboard;
use Livewire\Component;
use App\Helpers\Flash;

class Dashboard extends Component
{
    public $ciclo_escolar;
    public $periodo_escolar;

    public function guardarDatos()
    {
        $this->validate([
            'ciclo_escolar' => 'required',
            'periodo_escolar' => 'required',
        ]);

        // Siempre usa el registro con ID = 1 o crea uno nuevo si no existe
        ModelsDashboard::create(
            [
                'ciclo_escolar' => trim($this->ciclo_escolar),
                'periodo_escolar' => trim($this->periodo_escolar)
            ]
        );


        Flash::success('Datos guardados correctamente');
        $this->dispatch("refreshHeader");
    }

    public function mount()
    {
        $dashboard = ModelsDashboard::latest('id')->first(); // Obtenemos el último registro por ID
        $this->ciclo_escolar = $dashboard->ciclo_escolar ?? '';
        $this->periodo_escolar = $dashboard->periodo_escolar ?? '';

    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
