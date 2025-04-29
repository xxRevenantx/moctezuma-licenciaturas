<?php

namespace App\Livewire\Admin\Generacion;

use App\Models\Generacion;
use Livewire\Component;

class CrearGeneracion extends Component
{


    public $generacion;


    public function crearGeneracion(){
        $this->validate([
            'generacion' => 'required|string|max:9|unique:generaciones,generacion',
        ],[
            'generacion.required' => 'El campo generación es obligatorio.',
            'generacion.string' => 'El campo generación debe ser una cadena de texto.',
            'generacion.max' => 'El campo generación no puede tener más de 9 caracteres.',
            'generacion.unique' => 'La generacion ya existe en la base de datos.',
        ]);

        // Aquí puedes agregar la lógica para guardar la generación en la base de datos
        // Por ejemplo:
        Generacion::create(['generacion' => trim($this->generacion)]);

        $this->dispatch('swal', [
            'title' => 'Generación creada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        $this->reset('generacion');

        $this->dispatch('refreshGeneracion');
    }

    public function render()
    {
        return view('livewire.admin.generacion.crear-generacion');
    }
}
