<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use App\Services\Usuarios\UserManagementService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CrearUsuario extends Component
{
    public string $username = '';
    public string $email = '';
    public string $CURP = '';
    public array $rol = [];

    public function mount(): void
    {
        $service = app(UserManagementService::class);
        $service->authorize();
        $this->username = $this->generarUsernameUnico();
    }

    private function generarUsernameUnico(): string
    {
        do {
            $username = 'user_'.Str::random(5);
        } while (User::where('username', $username)->exists());

        return $username;
    }

    public function guardarUsuario(): void
    {
        $service = app(UserManagementService::class);
        $service->authorize();
        $this->username = trim($this->username);
        $this->email = Str::lower(trim($this->email));
        $this->CURP = Str::upper(trim($this->CURP));

        $data = $this->validate([
            'username' => ['required', 'string', 'max:15', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'CURP' => ['required', 'string', 'size:18', 'regex:/^[A-Z0-9]{18}$/', Rule::unique('users', 'CURP')],
            'rol' => ['required', 'array', 'min:1'],
            'rol.*' => ['required', 'integer', Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ], [
            'username.unique' => 'El nombre de usuario ya existe.',
            'email.unique' => 'El correo electrónico ya existe.',
            'CURP.unique' => 'La CURP ya está registrada.',
            'CURP.size' => 'La CURP debe tener 18 caracteres.',
            'rol.required' => 'Selecciona al menos un rol.',
        ]);

        $user = $service->crear($data, $this->rol);

        $this->reset(['email', 'CURP', 'rol']);
        $this->username = $this->generarUsernameUnico();
        $this->dispatch('usuario-creado');
        $this->dispatch('refreshUsuarios');
        $this->dispatch('swal', [
            'title' => 'Cuenta creada. Está pendiente de activación y contraseña.',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }

    public function render()
    {
        $service = app(UserManagementService::class);
        return view('livewire.admin.usuarios.crear-usuario', [
            'roles' => $service->rolesVisibles(),
        ]);
    }
}
