<?php

namespace App\Livewire\Admin\Usuarios;

use App\Exports\UsersExport;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class MostrarUsuarios extends Component
{
    use WithPagination;

    public $search = '';


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


    public static function placeholder(){
        return view('placeholder');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function exportarUsuarios()
{
    $usuariosFiltrados = User::where('username', 'like', '%' . $this->search . '%')
        ->orWhere('email', 'like', '%' . $this->search . '%')
        ->orderBy('id', 'desc')
        ->get(['order','username', 'email', 'status']); // columnas deseadas

    return Excel::download(new UsersExport($usuariosFiltrados), 'usuarios_filtrados.xlsx');
}


    #[On('refreshUsuarios')]
    public function render()
    {
        $usuarios = User::where('username', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('livewire.admin.usuarios.mostrar-usuarios', compact('usuarios'));
    }
}
