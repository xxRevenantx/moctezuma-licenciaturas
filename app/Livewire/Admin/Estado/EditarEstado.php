<?php

namespace App\Livewire\Admin\Estado;

use App\Models\Estado;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarEstado extends Component
{

    public $nombreId;
    public $nombre;
    public $open = false;


    #[On('abrirEstado')]
    public function abrirModal($id)
    {
        $estado = Estado::findOrFail($id);
        $this->nombreId = $estado->id;
        $this->nombre = $estado->nombre;
        $this->open = true;

    }

    public function actualizarEstado()
    {
        $this->validate([
            'nombre' => 'required|string|max:255|unique:estados,nombre,' . $this->nombreId,
        ], [
            'nombre.required' => 'El Estado es obligatorio.',
            'nombre.string' => 'El Estado debe ser una cadena de texto.',
            'nombre.max' => 'Este Estado no puede tener más de 255 caracteres.',
            'nombre.unique' => 'El Estado ' . $this->nombre . ' ya existe.',
        ]);



        $estado = Estado::findOrFail($this->nombreId);
        $estado->update([
            'nombre' => strtoupper(trim($this->nombre)),
        ]);


        $this->dispatch('refreshEstados');
        $this->dispatch('swal', [
            'title' => '¡Estado actualizado correctamente!',
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
        return view('livewire.admin.estado.editar-estado');
    }
}
