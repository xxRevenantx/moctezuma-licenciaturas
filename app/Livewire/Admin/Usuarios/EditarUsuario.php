<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

use Spatie\Permission\Models\Role;

class EditarUsuario extends Component
{


    public $usuario;
    public $open = false;
    public $userId;
    public $username;
    public $email;
    public $status;
    public $rol;


      // Método para abrir el modal con datos
      #[On('abrirModal')]
      public function abrirModal($id)
      {
            $this->usuario = User::findOrFail($id);
            $user = User::findOrFail($id);

            $this->userId = $user->id;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->status = $user->status == "true" ? true : false;
            $this->rol = $user->roles->pluck('id')->toArray();

          $this->open = true;
      }



      public function actualizarUsuario()
      {


          $this->validate([
                'username' => 'required|string|max:15|unique:users,username,' . $this->userId,
                'email' => 'required|email|max:50|unique:users,email,' . $this->userId,
                'status' => 'required|boolean',
                'rol' => 'required'
          ]);

          // ❗ Validación de seguridad para evitar que alguien asigne el rol SuperAdmin sin tenerlo
            $superAdminRoleId = \Spatie\Permission\Models\Role::where('name', 'SuperAdmin')->value('id');
            // Verificar que los inputs no tengan espacios a los lados
            $this->username = trim($this->username);
            $this->email = trim($this->email);

            if (in_array($superAdminRoleId, $this->rol) && !auth()->user()->hasRole('SuperAdmin')) {
                abort(403, 'No autorizado a asignar el rol SuperAdmin');
            }

            if($this->status == true) {
                $this->status = 'true';
            } else {
                $this->status = 'false';
            }



          $this->usuario->update([
              'username' => $this->username,
              'email' => $this->email,
              'status' => $this->status,
          ]);

          $this->usuario->roles()->sync($this->rol);

          $this->dispatch('swal', [
              'title' => '¡Usuario actualizado correctamente!',
              'icon' => 'success',
              'position' => 'top-end',
          ]);

          $this->dispatch('refreshUsuarios');

          $this->cerrarModal();
      }

      public function cerrarModal()
      {
          $this->reset(['open', 'userId', 'username', 'email', 'status', 'rol']);
          $this->resetValidation();
      }



    public function render()
    {
          // Si el usuario autenticado NO tiene el rol SuperAdmin, excluye ese rol
        if (!auth()->user()->hasRole('SuperAdmin')) {
            $roles = Role::where('name', '!=', 'SuperAdmin')->get();
        } else {
            $roles = Role::all();
        }

        return view('livewire.admin.usuarios.editar-usuario', compact('roles'));

    }
}
