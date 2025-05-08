<?php

namespace App\Livewire\Admin\Ciudad;

use App\Models\Ciudad;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarCiudad extends Component
{
    public $nombreId;
    public $nombre;
    public $open = false;


    #[On('abrirCiudad')]
    public function abrirModal($id)
    {
        $ciudad = Ciudad::findOrFail($id);
        $this->nombreId = $ciudad->id;
        $this->nombre = $ciudad->nombre;
        $this->open = true;

    }

    public function actualizarCiudad()
    {
        $this->validate([
            'nombre' => 'required|string|max:255|unique:ciudades,nombre,' . $this->nombreId,
        ], [
            'nombre.required' => 'El Ciudad es obligatorio.',
            'nombre.string' => 'El Ciudad debe ser una cadena de texto.',
            'nombre.max' => 'Este Ciudad no puede tener más de 255 caracteres.',
            'nombre.unique' => 'El Ciudad ' . $this->nombre . ' ya existe.',
        ]);



        $ciudad = Ciudad::findOrFail($this->nombreId);
        $ciudad->update([
            'nombre' => strtoupper(trim($this->nombre)),
        ]);


        $this->dispatch('refreshCiudades');
        $this->dispatch('swal', [
            'title' => '¡Ciudad actualizada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);



        $this->cerrarModal();
    }



    public function cerrarModal()
    {
        $this->reset(['open', 'nombreId', 'nombre']);
    }


    public function render()
    {
        return view('livewire.admin.ciudad.editar-ciudad');
    }
}
