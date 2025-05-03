<?php

namespace App\Livewire\Admin\Generacion;

use App\Models\Cuatrimestre;
use App\Models\Generacion;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarGeneracion extends Component
{

    public $generacionId;
    public $generacion;
    public $activa;
    public $open = false;

    #[On('abrirGeneracion')]
    public function abrirModal($id)
    {
        $generacion = Generacion::findOrFail($id);
        $this->generacionId = $generacion->id;
        $this->generacion = $generacion->generacion;
        $this->activa = $generacion->activa == "true" ? true : false;
        $this->open = true;
    }

    public function actualizarGeneracion()
    {
        $this->validate([
            'generacion' => 'required|string|max:9|unique:generaciones,generacion,' . $this->generacionId,
            'activa' => 'required',
        ],[
            'generacion.required' => 'El campo generación es obligatorio.',
            'generacion.string' => 'El campo generación debe ser una cadena de texto.',
            'generacion.max' => 'El campo generación no puede tener más de 9 caracteres.',
            'generacion.unique' => 'La generacion ya existe en la base de datos.',
            'activa.required' => 'El campo activa es obligatorio.',

        ]);

        if($this->activa == true){
            $this->activa = "true";
        }else{
            $this->activa = "false";
        }

        $generacion = Generacion::find($this->generacionId);
        if ($generacion) {
            $generacion->update([
                'generacion' => trim($this->generacion),
                'activa' => $this->activa,
            ]);

            $this->dispatch('swal', [
                'title' => 'Generación actualizada correctamente!',
                'icon' => 'success',
                'position' => 'top-end',
            ]);

            $this->reset(['open', 'generacionId', 'generacion', 'activa']);
            $this->dispatch('refreshGeneracion');
        }
    }
    public function cerrarModal()
    {
        $this->reset(['open', 'generacionId', 'generacion', 'activa']);
    }




    public function render()
    {

        return view('livewire.admin.generacion.editar-generacion');
    }
}
