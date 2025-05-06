<?php

namespace App\Livewire\Admin\Licenciaturas;

use App\Models\Accion;
use App\Models\Cuatrimestre;
use App\Models\Licenciatura;
use Livewire\Component;

class Submodulo extends Component
{


    public $licenciatura;
    public $modalidad;
    public $submodulo;
    public $acciones;
    public $cuatrimestres;

    public function mount($slug, $modalidad, $submodulo)
    {
        $this->licenciatura = Licenciatura::where('slug', $slug)->firstOrFail();
        $this->modalidad = $modalidad;
        $this->submodulo = $submodulo;
        $this->acciones = Accion::all();
        $this->cuatrimestres = Cuatrimestre::all();
    }

    public function guardarEstudiante(){
        dd("Guardando estudiante");
    }

    public function render()
    {
        return view("livewire.admin.licenciaturas.submodulo.{$this->submodulo}", [
            'licenciatura' => $this->licenciatura,
            'modalidad' => $this->modalidad,
            'acciones' => $this->acciones,

        ]);
    }
}
