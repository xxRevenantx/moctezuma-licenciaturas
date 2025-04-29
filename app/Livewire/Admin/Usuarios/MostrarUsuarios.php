<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

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


    public static function placeholder(){
        return view('placeholder');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }


    #[On('refreshUsuarios')]
    public function render()
    {
        $usuarios = User::where('username', 'like', '%' . $this->search . '%')
             ->orWhere('matricula', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('livewire.admin.usuarios.mostrar-usuarios', compact('usuarios'));
    }
}
