<?php

namespace App\Livewire\Admin\Estado;

use App\Models\Estado;
use Livewire\Component;

class CrearEstado extends Component
{

    public $nombre;



    public function crearEstado(){
        $this->validate([
            'nombre' => 'required|string|max:255',
        ],[
            'nombre.required' => 'El campo Estado es obligatorio.',
            'nombre.string' => 'El campo Estado debe ser una cadena de texto.',
            'nombre.max' => 'El campo Estado no puede tener más de 255 caracteres.',
        ]);

        Estado::create([
            'nombre' => strtoupper(trim($this->nombre)),
        ]);
        $this->reset('nombre');

        $this->dispatch('swal', [
            'title' => '¡Estado creado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        $this->dispatch('refreshEstados');


    }

    public function render()
    {
        return view('livewire.admin.estado.crear-estado');
    }
}
