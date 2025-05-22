<?php

namespace App\Livewire\Admin\Cuatrimestre;

use App\Models\Cuatrimestre;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarCuatrimestre extends Component
{
    public $cuatrimestreId;
    public $cuatrimestre;
    public $nombre_cuatrimestre;
    public $mes_id;
    public $open = false;


    #[On('abrirCuatrimestre')]
    public function abrirModal($id)
    {
        $cuatrimestre = Cuatrimestre::findOrFail($id);
        $this->cuatrimestreId = $cuatrimestre->id;
        $this->cuatrimestre = $cuatrimestre->cuatrimestre;
        $this->nombre_cuatrimestre = $cuatrimestre->nombre_cuatrimestre;
        $this->mes_id = $cuatrimestre->mes_id;

        $this->open = true;

    }

    public function actualizarCuatrimestre()
    {
        $this->validate([
            'cuatrimestre' => 'required|numeric|min:1|max:9|unique:cuatrimestres,cuatrimestre,' . $this->cuatrimestreId,
            'nombre_cuatrimestre' => 'required|string|max:15|unique:cuatrimestres,nombre_cuatrimestre,' . $this->cuatrimestreId,
            'mes_id' => 'required|exists:meses,id',
        ], [

            'cuatrimestre.unique' => 'El cuatrimestre ya existe.',
            'cuatrimestre.required' => 'El campo cuatrimestre es obligatorio.',
            'cuatrimestre.numeric' => 'El campo cuatrimestre debe ser un número.',
            'cuatrimestre.min' => 'El cuatrimestre debe ser al menos 1.',
            'cuatrimestre.max' => 'El cuatrimestre no puede ser mayor a 10.',
            'nombre_cuatrimestre.required' => 'El campo nombre del cuatrimestre es obligatorio.',
            'nombre_cuatrimestre.string' => 'El campo nombre del cuatrimestre debe ser una cadena de texto.',
            'nombre_cuatrimestre.max' => 'El campo nombre del cuatrimestre no puede tener más de 15 caracteres.',
            'nombre_cuatrimestre.unique' => 'El nombre del cuatrimestre ya existe.',
            'mes_id.required' => 'El campo meses es obligatorio.',
            'mes_id.exists' => 'El mes seleccionado no es válido.',

        ]);



        $asignacion = Cuatrimestre::findOrFail($this->cuatrimestreId);
        $asignacion->update([
            'cuatrimestre' => $this->cuatrimestre,
            'nombre_cuatrimestre' => strtoupper(trim($this->nombre_cuatrimestre)),
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
          $this->resetValidation();
    }


    public function render()
    {
        $meses = \App\Models\Mes::all();
        return view('livewire.admin.cuatrimestre.editar-cuatrimestre', compact('meses'));
    }
}
