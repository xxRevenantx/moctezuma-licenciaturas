<?php

namespace App\Livewire\Admin\Generacion;

use App\Models\Generacion;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarGeneracion extends Component
{

    public $generacionId;
    public $generacion;
    public $egresada;
    public $open = false;

    #[On('abrirGeneracion')]
    public function abrirModal($id)
    {
        $generacion = Generacion::findOrFail($id);
        $this->generacionId = $generacion->id;
        $this->generacion = $generacion->generacion;
        $this->egresada = $generacion->egresada == "true" ? true : false;
        $this->open = true;
    }

    public function actualizarGeneracion()
    {
        $this->validate([
            'generacion' => 'required|string|max:9|unique:generaciones,generacion,' . $this->generacionId,
            'egresada' => 'required',
        ],[
            'generacion.required' => 'El campo generación es obligatorio.',
            'generacion.string' => 'El campo generación debe ser una cadena de texto.',
            'generacion.max' => 'El campo generación no puede tener más de 9 caracteres.',
            'generacion.unique' => 'La generacion ya existe en la base de datos.',
            'egresada.required' => 'El campo egresada es obligatorio.',
        ]);

        if($this->egresada == true){
            $this->egresada = "true";
        }else{
            $this->egresada = "false";
        }

        $generacion = Generacion::find($this->generacionId);
        if ($generacion) {
            $generacion->update([
                'generacion' => trim($this->generacion),
                'egresada' => $this->egresada,
            ]);

            $this->dispatch('swal', [
                'title' => 'Generación actualizada correctamente!',
                'icon' => 'success',
                'position' => 'top-end',
            ]);

            $this->reset(['open', 'generacionId', 'generacion', 'egresada']);
            $this->dispatch('refreshGeneracion');
        }
    }
    public function cerrarModal()
    {
        $this->reset(['open', 'generacionId', 'generacion', 'egresada']);
    }




    public function render()
    {
        return view('livewire.admin.generacion.editar-generacion');
    }
}
