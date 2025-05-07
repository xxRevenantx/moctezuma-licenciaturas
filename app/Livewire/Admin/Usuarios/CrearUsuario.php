<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class CrearUsuario extends Component
{

    public $username;
    public $email;
    public $rol;



    public function mount()
    {
        $this->username = $this->generarUsernameUnico();
    }

    private function generarUsernameUnico(): string
    {
        do {
            $username = 'user_' . Str::random(5);
        } while (User::where('username', $username)->exists());

        return $username;
    }




    public function guardarUsuario(){



        $this->validate([
            'username' => 'required|unique:users,username|max:15',
            'email' => 'required|email|unique:users,email',
            'rol' => 'required',
        ]);



        // Aquí puedes agregar la lógica para guardar el usuario en la base de datos

        $user = User::create([
            'username' => trim($this->username),
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
