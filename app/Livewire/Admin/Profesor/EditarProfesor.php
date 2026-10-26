<?php

namespace App\Livewire\Admin\Profesor;

use App\Models\Profesor;
use App\Services\Profesores\ProfesorManagementService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditarProfesor extends Component
{
    use WithFileUploads;

    public bool $open = false;
    public ?int $profesorId = null;
    public $nombre = '';
    public $apellido_paterno = '';
    public $apellido_materno = '';
    public $telefono = '';
    public $perfil = '';
    public $color = '';
    public $foto;
    public $foto_nueva;
    public string $estadoCuenta = '';
    public string $estadoDocente = '';

    #[On('abrirProfesor')]
    public function abrirModal($id): void
    {
        app(ProfesorManagementService::class)->autorizar();
        $p = Profesor::with('user')->findOrFail((int) $id);
        $this->profesorId = $p->id;
        $this->nombre = $p->nombre;
        $this->apellido_paterno = $p->apellido_paterno;
        $this->apellido_materno = $p->apellido_materno;
        $this->telefono = $p->telefono;
        $this->perfil = $p->perfil;
        $this->color = $p->color ?: '#0284c7';
        $this->foto = $p->foto;
        $this->estadoCuenta = $p->user?->status === 'true' ? 'Activa' : 'Pendiente o inactiva';
        $this->estadoDocente = $p->status === 'true' ? 'Disponible' : 'No disponible';
        $this->reset('foto_nueva');
        $this->resetValidation();
        $this->open = true;
    }

    public function actualizarProfesor(ProfesorManagementService $service): void
    {
        $service->autorizar();
        abort_unless($this->profesorId, 404);
        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'perfil' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'foto_nueva' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,jpg,png'],
        ], ['telefono.regex' => 'El teléfono debe contener exactamente 10 dígitos.']);
        $service->actualizar($this->profesorId, $validated, $this->foto_nueva);
        $this->dispatch('swal', ['title' => 'Profesor actualizado correctamente.', 'icon' => 'success', 'position' => 'top-end']);
        $this->dispatch('refreshProfesor');
        $this->cerrarModal();
    }

    public function cerrarModal(): void
    {
        $this->reset(['open', 'profesorId', 'nombre', 'apellido_paterno', 'apellido_materno', 'telefono', 'perfil', 'color', 'foto', 'foto_nueva', 'estadoCuenta', 'estadoDocente']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.profesor.editar-profesor');
    }
}
