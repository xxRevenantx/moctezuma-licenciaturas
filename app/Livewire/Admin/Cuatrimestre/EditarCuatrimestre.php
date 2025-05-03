<?php

namespace App\Livewire\Admin\Cuatrimestre;

use App\Models\Cuatrimestre;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarCuatrimestre extends Component
{
    public $cuatrimestreId;
    public $cuatrimestre;
    public $mes_id;
    public $open = false;


    #[On('abrirCuatrimestre')]
    public function abrirModal($id)
    {
        $cuatrimestre = Cuatrimestre::findOrFail($id);
        $this->cuatrimestreId = $cuatrimestre->id;
        $this->cuatrimestre = $cuatrimestre->cuatrimestre;
        $this->mes_id = $cuatrimestre->mes_id;

        $this->open = true;

    }

    public function actualizarCuatrimestre()
    {
        $this->validate([
            'cuatrimestre' => 'required|numeric|min:1|max:9|unique:cuatrimestres,cuatrimestre,' . $this->cuatrimestreId,
            'mes_id' => 'required|exists:meses,id',
        ], [

            'cuatrimestre.unique' => 'El cuatrimestre ya existe.',
            'cuatrimestre.required' => 'El campo cuatrimestre es obligatorio.',
            'cuatrimestre.numeric' => 'El campo cuatrimestre debe ser un número.',
            'cuatrimestre.min' => 'El cuatrimestre debe ser al menos 1.',
            'cuatrimestre.max' => 'El cuatrimestre no puede ser mayor a 10.',
            'mes_id.required' => 'El campo meses es obligatorio.',
            'mes_id.exists' => 'El mes seleccionado no es válido.',

        ]);



        $asignacion = Cuatrimestre::findOrFail($this->cuatrimestreId);
        $asignacion->update([
            'cuatrimestre' => $this->cuatrimestre,
            'mes_id' => $this->mes_id,
        ]);


        $this->dispatch('refreshCuatrimestre');

        $this->dispatch('swal', [
            'title' => 'Cuatrimestre actualizada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);



        $this->cerrarModal();
    }



    public function cerrarModal()
    {
        $this->reset(['open', 'cuatrimestreId', 'cuatrimestre', 'mes_id']);
    }


    public function render()
    {
        $meses = \App\Models\Mes::all();
        return view('livewire.admin.cuatrimestre.editar-cuatrimestre', compact('meses'));
    }
}
