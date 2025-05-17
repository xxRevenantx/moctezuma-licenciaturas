<?php

namespace App\Livewire\Admin\Profesor;

use App\Models\Profesor;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditarProfesor extends Component
{

    use WithFileUploads;

     public $profesor;
    public $profesorId;
    public $nombre;
    public $apellido_paterno;
    public $apellido_materno;
    public $telefono;
    public $perfil;
    public $color;
    public $foto;
    public $foto_nueva;


    public $open = false;


    // Método para abrir el modal con datos
    #[On('abrirProfesor')]
    public function abrirModal($id)
    {
        $profesor = Profesor::findOrFail($id);

        $this->profesorId = $profesor->id;
        $this->nombre = $profesor->nombre;
        $this->apellido_paterno = $profesor->apellido_paterno;
        $this->apellido_materno = $profesor->apellido_materno;
        $this->telefono = $profesor->telefono;
        $this->perfil = $profesor->perfil;
        $this->color = $profesor->color;
        $this->foto = $profesor->foto;
        $this->open = true;

    }

     public function actualizarProfesor()
    {
        $this->validate(
            [
                'nombre' => 'required|string|max:255',
                'apellido_paterno' => 'required|string|max:255',
                'apellido_materno' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:10',
                'perfil' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:255',
                'foto_nueva' => 'nullable|image|max:2048|mimes:jpeg,jpg,png',
            ],
            [
                'nombre.required' => 'El nombre es obligatorio.',
                'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
                'telefono.max' => 'El teléfono no puede tener más de 10 caracteres.',
                'perfil.max' => 'El perfil no puede tener más de 255 caracteres.',
                'color.max' => 'El color no puede tener más de 255 caracteres.',
                'apellido_materno.max' => 'El apellido materno no puede tener más de 255 caracteres.',
                'apellido_materno.string' => 'El apellido materno debe ser una cadena de texto.',
                'apellido_paterno.string' => 'El apellido paterno debe ser una cadena de texto.',
                'nombre.string' => 'El nombre debe ser una cadena de texto.',
                'telefono.string' => 'El teléfono debe ser una cadena de texto.',
                'perfil.string' => 'El perfil debe ser una cadena de texto.',
                'color.string' => 'El color debe ser una cadena de texto.',
                'foto_nueva.image' => 'La foto nueva debe ser una imagen.',
                'foto_nueva.max' => 'La foto nueva no debe exceder 2 MB.',
                'foto_nueva.mimes' => 'La foto nueva debe ser de tipo jpeg, jpg o png.',
            ],

        );

        if ($this->foto_nueva) {
            // Eliminar la imagen anterior si existe
            if ($this->foto) {
            Storage::delete('profesores/' . $this->foto);
            }

            $foto = $this->foto_nueva->store('profesores');
            $datos['foto'] = str_replace('profesores/', '', $foto);
        }

        // Aquí puedes agregar la lógica para actualizar el directivo en la base de datos
        // Por ejemplo:
        $profesor = Profesor::find($this->profesorId);
        $profesor->update([
            'nombre' => strtoupper(trim($this->nombre)),
            'apellido_paterno' => strtoupper(trim($this->apellido_paterno)),
            'apellido_materno' => strtoupper(trim($this->apellido_materno)),
            'telefono' => trim($this->telefono),
            'perfil' => strtoupper(trim($this->perfil)),
            'color' => $this->color,
            'foto' => $this->foto_nueva ? $datos['foto'] : $this->foto,
        ]);
        $this->dispatch('swal', [
            'title' => 'Profesor actualizado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        $this->dispatch('refreshProfesor');
        $this->cerrarModal();
    }

     public function cerrarModal()
    {
        $this->reset(['open', 'profesorId', 'nombre', 'apellido_paterno', 'apellido_materno', 'telefono', 'perfil', 'color', 'foto', 'foto_nueva']);
        $this->resetValidation();
    }



    public function render()
    {
        return view('livewire.admin.profesor.editar-profesor');
    }
}
