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

    public bool $open = false;

    public ?int $profesorId = null;
    public $nombre;
    public $apellido_paterno;
    public $apellido_materno;
    public $telefono;
    public $perfil;
    public $color;
    public $foto;       // nombre del archivo actual
    public $foto_nueva; // temporaria

    // Abre el modal con datos
    #[On('abrirProfesor')]
    public function abrirModal($id): void
    {
        $p = Profesor::findOrFail($id);

        $this->profesorId       = $p->id;
        $this->nombre           = $p->nombre;
        $this->apellido_paterno = $p->apellido_paterno;
        $this->apellido_materno = $p->apellido_materno;
        $this->telefono         = $p->telefono;
        $this->perfil           = $p->perfil;
        $this->color            = $p->color;
        $this->foto             = $p->foto;

        $this->open = true;
    }

    public function actualizarProfesor(): void
    {
        $this->validate(
            [
                'nombre'          => 'required|string|max:255',
                'apellido_paterno'=> 'required|string|max:255',
                'apellido_materno'=> 'nullable|string|max:255',
                'telefono'        => 'nullable|string|max:10',
                'perfil'          => 'nullable|string|max:255',
                'color'           => 'nullable|string|max:255',
                'foto_nueva'      => 'nullable|image|max:2048|mimes:jpeg,jpg,png',
            ],
            [
                'nombre.required'           => 'El nombre es obligatorio.',
                'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
                'telefono.max'              => 'El teléfono no puede tener más de 10 caracteres.',
                'perfil.max'                => 'El perfil no puede tener más de 255 caracteres.',
                'color.max'                 => 'El color no puede tener más de 255 caracteres.',
                'foto_nueva.image'          => 'La foto nueva debe ser una imagen.',
                'foto_nueva.max'            => 'La foto nueva no debe exceder 2 MB.',
                'foto_nueva.mimes'          => 'La foto nueva debe ser de tipo jpeg, jpg o png.',
            ],
        );

        $p = Profesor::findOrFail($this->profesorId);

        // manejar imagen en disco public
        if ($this->foto_nueva) {
            if ($this->foto) {
                Storage::disk('public')->delete('profesores/' . $this->foto);
            }
            $path = $this->foto_nueva->store('profesores', 'public'); // storage/app/public/profesores
            $this->foto = basename($path);
        }

        $p->update([
            'nombre'            => strtoupper(trim((string)$this->nombre)),
            'apellido_paterno'  => strtoupper(trim((string)$this->apellido_paterno)),
            'apellido_materno'  => strtoupper((string)($this->apellido_materno ?? '')),
            'telefono'          => trim((string)$this->telefono),
            'perfil'            => strtoupper(trim((string)$this->perfil)),
            'color'             => $this->color,
            'foto'              => $this->foto, // ya actualizado si hubo nueva
        ]);

        $this->dispatch('swal', [
            'title'    => '¡Profesor actualizado correctamente!',
            'icon'     => 'success',
            'position' => 'top-end',
        ]);

        $this->dispatch('refreshProfesor');
        $this->cerrarModal();
    }

    public function cerrarModal(): void
    {
        $this->reset([
            'open',
            'profesorId',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'telefono',
            'perfil',
            'color',
            'foto',
            'foto_nueva',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.profesor.editar-profesor');
    }
}
