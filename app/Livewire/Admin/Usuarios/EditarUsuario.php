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
    public $CURP;
    public $status;
    public $rol;

    public $rol_name;

    public $toggle = false;


      // Método para abrir el modal con datos
      #[On('abrirModal')]
      public function abrirModal($id)
      {
            $this->usuario = User::findOrFail($id);
            $user = User::findOrFail($id);

            $this->userId = $user->id;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->CURP = $user->CURP;
            $this->status = $user->status == "true" ? true : false;
            $this->rol = $user->roles->pluck('id')->toArray();

            $this->rol_name = $user->roles->pluck('name')->implode(', ');

          $this->open = true;
      }


      // TOGGLE STATUS
      public function toggleStatus(){
            $this->toggle = true;
      }


      public function actualizarUsuario()
      {


          $this->validate([
                'username' => 'required|string|max:15|unique:users,username,' . $this->userId,
                'email' => 'required|email|max:50|unique:users,email,' . $this->userId,
                'CURP' => 'required|max:18|unique:users,CURP,' . $this->userId,
                'status' => 'required|boolean',
                'rol' => 'required'
          ],[
                'username.required' => 'El nombre de usuario es obligatorio.',
                'username.unique' => 'El nombre de usuario ya está en uso.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo electrónico no es válido.',
                'email.unique' => 'El correo electrónico ya está en uso.',
                'CURP.required' => 'El CURP es obligatorio.',
                'CURP.unique' => 'El CURP ya está en uso.',
                'rol.required' => 'Debes seleccionar al menos un rol.',
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
              'email' => trim($this->email),
              'CURP' => strtoupper(trim($this->CURP)),
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
          $this->reset(['open', 'userId', 'username', 'email', 'CURP', 'status', 'rol']);
          $this->toggle = false;
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
