<?php

namespace App\Livewire\Admin\AsignarGeneracion;

use App\Models\AsignarGeneracion;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarGeneracion extends Component
{
    public $asignacionId;
    public $licenciatura_id;
    public $modalidad_id;
    public $generacion_id;
    public $open = false;


    #[On('abrirAsignacion')]
    public function abrirModal($id)
    {
        $asignacion = AsignarGeneracion::findOrFail($id);
        $this->asignacionId = $asignacion->id;
        $this->licenciatura_id = $asignacion->licenciatura_id;
        $this->modalidad_id = $asignacion->modalidad_id;
        $this->generacion_id = $asignacion->generacion_id;
        $this->open = true;


    }




    public function cerrarModal()
    {
        $this->reset(['open', 'asignacionId', 'licenciatura_id', 'modalidad_id', 'generacion_id']);
    }


    public function render()
    {
        $licenciaturas = \App\Models\Licenciatura::all();
        $generaciones = \App\Models\Generacion::all();
        return view('livewire.admin.asignar-generacion.editar-generacion', compact('licenciaturas', 'generaciones'));
    }
}
