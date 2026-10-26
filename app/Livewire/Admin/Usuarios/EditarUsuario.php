<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use App\Services\Usuarios\UserManagementService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarUsuario extends Component
{
    public bool $open = false;
    public ?int $userId = null;
    public string $username = '';
    public string $email = '';
    public string $CURP = '';
    public bool $status = false;
    public array $rol = [];
    public string $rol_name = '';

    #[On('abrirModal')]
    public function abrirModal($id): void
    {
        $service = app(UserManagementService::class);
        $actor = $service->authorize();
        $user = User::with('roles')->findOrFail((int) $id);
        abort_if($user->hasRole('SuperAdmin') && ! $actor->hasRole('SuperAdmin'), 403);

        $this->resetValidation();
        $this->userId = $user->id;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->CURP = $user->CURP;
        $this->status = $user->status === 'true';
        $this->rol = $user->roles->pluck('id')->all();
        $this->rol_name = $user->roles->pluck('name')->implode(', ');
        $this->open = true;
    }

    public function actualizarUsuario(): void
    {
        $service = app(UserManagementService::class);
        $service->authorize();
        abort_unless($this->userId, 404);
        $this->username = trim($this->username);
        $this->email = Str::lower(trim($this->email));
        $this->CURP = Str::upper(trim($this->CURP));

        $data = $this->validate([
            'username' => ['required', 'string', 'max:15', Rule::unique('users', 'username')->ignore($this->userId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            // Permitir modificar perfiles legados con CURP incompleta sin forzar una migración masiva.
            'CURP' => ['required', 'string', 'max:18', Rule::unique('users', 'CURP')->ignore($this->userId)],
            'status' => ['required', 'boolean'],
            'rol' => ['required', 'array', 'min:1'],
            'rol.*' => ['required', 'integer', Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ]);
        $service->actualizar($this->userId, $data, $this->rol);

        $this->dispatch('refreshUsuarios');
        $this->dispatch('swal', [
            'title' => 'Usuario actualizado correctamente.',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
        $this->cerrarModal();
    }

    public function cerrarModal(): void
    {
        $this->reset(['open', 'userId', 'username', 'email', 'CURP', 'status', 'rol', 'rol_name']);
        $this->resetValidation();
    }

    public function render()
    {
        $service = app(UserManagementService::class);
        return view('livewire.admin.usuarios.editar-usuario', [
            'roles' => $service->rolesVisibles(),
        ]);
    }
}
