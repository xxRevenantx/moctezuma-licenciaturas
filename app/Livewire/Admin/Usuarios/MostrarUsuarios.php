<?php

namespace App\Livewire\Admin\Usuarios;

use App\Exports\UsersExport;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class MostrarUsuarios extends Component
{
    use WithPagination;

    public $search = '';

    public $rol;
    public $roles;
    public $filtrar_status;
    public $filtrar_roles;

    public $contar_super;
    public $obtener_super;

    public $selected = [];
    public $selectAll = false;



       public function updatedSelectAll($value)
    {
        if ($value) {
            $query = User::query();

            if ($this->filtrar_status) {
                $query->where('status', $this->filtrar_status == 'Activo' ? 'true' : 'false');
            }
            if ($this->filtrar_roles) {
                $query->whereHas('roles', function ($query) {
                    $query->where('name', $this->filtrar_roles);
                });
            }
            if ($this->search) {
                $query->where(function ($query) {
                    $query->where('email', 'like', '%' . $this->search . '%')
                        ->orWhere('username', 'like', '%' . $this->search . '%')
                        ->orWhere('CURP', 'like', '%' . $this->search . '%')
                        ->orWhereHas('roles', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            }

            // Exclude users with the "SuperAdmin" role
            $query->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'SuperAdmin');
            });

            $this->selected = $query->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }


    // CAMBIO DE ROL
    public function cambioRolUsuariosSeleccionados($id){

        $usuarios = User::whereIn('id', $this->selected)->get();

        foreach ($usuarios as $usuario) {
            $usuario->roles()->sync($id);
        }

        $this->dispatch('swal', [
            'title' => '¡Rol cambiado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
        $this->selected = [];
        $this->selectAll = false;
        $this->limpiarFiltros();


    }



    public function mount(){
        $this->roles = Role::all();

        $this->contar_super = User::role('SuperAdmin')->count();

        $this->obtener_super = User::role('SuperAdmin')->get();
    }

     public function getUsuariosProperty()
    {

        $query = User::orderBy('id', 'desc');

        if ($this->filtrar_status) {
            $query->where('status', $this->filtrar_status == 'Activo' ? 'true' : 'false');
        }

        if ($this->filtrar_roles) {
            $query->whereHas('roles', function ($query) {
            $query->where('name', $this->filtrar_roles);
            });
        }

        if ($this->search) {
            $query->where(function ($query) {
            $query->where('email', 'like', '%' . $this->search . '%')
                ->orWhere('CURP', 'like', '%' . $this->search . '%')
                ->orWhere('username', 'like', '%' . $this->search . '%')
                ->orWhereHas('roles', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Exclude users with the "SuperAdmin" role
        $query->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'SuperAdmin');
        });

        return $query->paginate(15);
    }

    public function eliminarUsuario($id)
    {
        $user = User::find($id);

        if ($user) {
            // Eliminar la imagen asociada si existe
            $imagePath = public_path('storage/profile-photos/' . $user->imagen);
            if ($user->imagen && file_exists($imagePath)) {
            unlink($imagePath);
            }

            $user->delete();

            $this->dispatch('swal', [
            'title' => 'Usuario eliminado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
            ]);
        }
    }

    public function inactivarUsuarios()
    {
        $user = User::all();
        foreach ($user as $u) {
            if ($u->roles()->whereIn('name', ['Estudiante', 'Profesor', 'Invitado'])->exists()) {
                $u->status = 'false';
                $u->save();
            }
        }
        $this->dispatch('swal', [
            'title' => 'Usuarios inactivados correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }

    public function activarUsuarios()
    {
        $user = User::all();

        foreach ($user as $u) {
            if ($u->roles()->whereIn('name', ['Estudiante', 'Profesor', 'Invitado'])->exists()) {
                $u->status = 'true';
                $u->save();
            }
        }
        $this->dispatch('swal', [
            'title' => 'Usuarios activados correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->filtrar_status = null;
        $this->filtrar_roles = null;

    }


    public static function placeholder(){
        return view('placeholder');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function exportarUsuarios()
{
    $usuariosFiltrados = User::query()
        ->when($this->filtrar_status, function ($query) {
            $query->where('status', $this->filtrar_status == 'Activo' ? 'true' : 'false');
        })
        ->when($this->filtrar_roles, function ($query) {
            $query->whereHas('roles', function ($query) {
                $query->where('name', $this->filtrar_roles);
            });
        })
        ->where(function ($query) {
            $query->where('username', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->orWhereHas('roles', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                });
        })
        ->orderBy('id', 'desc')
        ->get();

    return Excel::download(new UsersExport($usuariosFiltrados), 'usuarios_filtrados.xlsx');
}





    #[On('refreshUsuarios')]
    public function render()
    {

        return view('livewire.admin.usuarios.mostrar-usuarios', [
            'usuarios' => $this->usuarios,
        ]);
    }
}
