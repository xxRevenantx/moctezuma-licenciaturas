<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Inscripcion;
use Livewire\Component;

class ListasGenerales extends Component
{

    public $licenciatura_id = null;
    public $alumnos;
    public $licenciatura_nombre;

    public function consultarListas(){
       $this->validate([
           'licenciatura_id' => 'required|exists:licenciaturas,id',
       ],[
           'licenciatura_id.required' => 'Debes seleccionar una licenciatura.',
           'licenciatura_id.exists' => 'La licenciatura seleccionada no es válida.',
       ]);

       // Consultar alumnos por licenciatura
         $this->alumnos = Inscripcion::where('licenciatura_id', $this->licenciatura_id)
            ->with('licenciatura')
            ->get();

         $this->licenciatura_nombre = \App\Models\Licenciatura::find($this->licenciatura_id);





    }

    public function render()
    {


        $licenciaturas = \App\Models\Licenciatura::all();
        return view('livewire.admin.documentacion.listas-generales', [
            'licenciaturas' => $licenciaturas,
        ]);
    }
}
