<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Inscripcion;
use Livewire\Component;

class Titulacion extends Component
{

    public $alumnos;
    public $lugar_registro;

    public function mount()
    {
        $this->alumnos = Inscripcion::where('status', 'true')->get();
        $this->lugar_registro = 'Chilpancingo de los Bravo, Guerrero';
    }

    public function render()
    {
        return view('livewire.admin.documentacion.titulacion');
    }
}
