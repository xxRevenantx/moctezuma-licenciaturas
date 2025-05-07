<?php

namespace App\Livewire\Admin\Ciudad;

use App\Models\Ciudad;
use Livewire\Component;

class CrearCiudad extends Component
{

    public $nombre;



    public function crearCiudad(){
        $this->validate([
            'nombre' => 'required|string|max:255',
        ],[
            'nombre.required' => 'La Ciudad es obligatoria.',
            'nombre.string' => 'El nombre de la Ciudad debe ser una cadena de texto.',
            'nombre.max' => 'El nombre de la Ciudad no debe exceder los 255 caracteres.',
        ]);

        Ciudad::create([
            'nombre' => strtoupper(trim($this->nombre)),
        ]);
        $this->reset('nombre');

        $this->dispatch('swal', [
            'title' => '¡Ciudad creada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        $this->dispatch('refreshCiudades');


    }



    public function render()
    {
        return view('livewire.admin.ciudad.crear-ciudad');
    }
}
