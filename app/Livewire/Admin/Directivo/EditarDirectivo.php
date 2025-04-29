<?php

namespace App\Livewire\Admin\Directivo;

use App\Models\Directivo;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarDirectivo extends Component
{

    public $directivoId;
    public $titulo;
    public $nombre;
    public $apellido_paterno;
    public $apellido_materno;
    public $telefono;
    public $correo;
    public $cargo;
    public $open = false;


    // Método para abrir el modal con datos
    #[On('abrirDirectivo')]
    public function abrirModal($id)
    {
        $directivo = Directivo::findOrFail($id);

        $this->directivoId = $directivo->id;
        $this->titulo = $directivo->titulo;
        $this->nombre = $directivo->nombre;
        $this->apellido_paterno = $directivo->apellido_paterno;
        $this->apellido_materno = $directivo->apellido_materno;
        $this->telefono = $directivo->telefono;
        $this->correo = $directivo->correo;
        $this->cargo = $directivo->cargo;

        $this->open = true;

    }



    public function actualizarDirectivo()
    {
        $this->validate(
            [
                'titulo' => 'required|string|max:255',
                'nombre' => 'required|string|max:255',
                'apellido_paterno' => 'required|string|max:255',
                'apellido_materno' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:255',
                'correo' => 'nullable|email|max:255',
                'cargo' => 'required|string|max:255',
            ],
            [
                'titulo.required' => 'El título es obligatorio.',
                'nombre.required' => 'El nombre es obligatorio.',
                'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
                'telefono.max' => 'El teléfono no debe exceder 255 caracteres.',
                'correo.email' => 'El correo electrónico no es válido.',
                'cargo.required' => 'El cargo es obligatorio.',
            ]
        );

        // Aquí puedes agregar la lógica para actualizar el directivo en la base de datos
        // Por ejemplo:
        $directivo = Directivo::find($this->directivoId);
        $directivo->update([
            'titulo' => $this->titulo,
            'nombre' => $this->nombre,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'cargo' => $this->cargo,
        ]);
        $this->dispatch('swal', [
            'title' => '¡Directivo actualizado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        $this->dispatch('refreshDirectivos');
        $this->cerrarModal();
    }




    public function cerrarModal()
    {
        $this->reset(['open', 'directivoId', 'titulo', 'nombre', 'apellido_paterno', 'apellido_materno', 'telefono', 'correo', 'cargo']);
        $this->resetValidation();
    }


    public function render()
    {
        return view('livewire.admin.directivo.editar-directivo');
    }
}
