<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class CrearUsuario extends Component
{

    public $username;
    public $matricula;
    public $email;
    public $rol;



    public function mount()
    {
        $this->username = $this->generarUsernameUnico();
        $this->matricula = $this->generarMatriculaUnica();
    }

    private function generarUsernameUnico(): string
    {
        do {
            $username = 'user_' . Str::random(5);
        } while (User::where('username', $username)->exists());

        return $username;
    }

    private function generarMatriculaUnica(): string
    {
        do {
            $matricula = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (User::where('matricula', $matricula)->exists());

        return $matricula;
    }


    public function guardarUsuario(){



        $this->validate([
            'username' => 'required|unique:users,username',
            'matricula' => 'required|unique:users,matricula|size:8',
            'email' => 'required|email|unique:users,email',
            'rol' => 'required',
        ]);



        // Aquí puedes agregar la lógica para guardar el usuario en la base de datos

        $user = User::create([
            'username' => trim($this->username),
            'matricula' => trim($this->matricula),
            'email' => trim($this->email),
            'password' => bcrypt('12345678'), // Cambia esto según tus necesidades
            'status' => 'true',
            'photo' => null,

        ]);

        // Asignar el rol al usuario
        $user->roles()->sync($this->rol);


        $this->dispatch('swal', [
            'title' => '¡Usuario creado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        // Limpiar los campos después de guardar
        $this->username = $this->generarUsernameUnico();
        $this->matricula = $this->generarMatriculaUnica();
        $this->email = '';
        $this->rol = [];

        $this->dispatch('refreshUsuarios');

    }


    public function render()
    {
        $roles = \Spatie\Permission\Models\Role::all();
        return view('livewire.admin.usuarios.crear-usuario', compact('roles'));
    }
}
