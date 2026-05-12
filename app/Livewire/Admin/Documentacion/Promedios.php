<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\AsignarGeneracion;
use App\Models\Generacion;
use App\Models\Licenciatura;
use Livewire\Component;

class Promedios extends Component
{
    public $licenciatura_id = '';
    public $generacion_id = '';

    public $licenciaturas = [];
    public $generaciones = [];

    public function mount()
    {
        $this->licenciaturas = Licenciatura::query()
            ->orderBy('nombre', 'asc')
            ->get();

        $this->generaciones = collect();
    }

    public function updatedLicenciaturaId()
    {
        $this->generacion_id = '';

        if (!$this->licenciatura_id) {
            $this->generaciones = collect();
            return;
        }

        $generacionIds = AsignarGeneracion::query()
            ->where('licenciatura_id', $this->licenciatura_id)
            ->pluck('generacion_id')
            ->unique()
            ->values();

        $this->generaciones = Generacion::query()
            ->whereIn('id', $generacionIds)
            ->orderBy('generacion', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.documentacion.promedios');
    }
}
