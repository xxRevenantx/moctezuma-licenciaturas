<?php

namespace App\Livewire\Admin\Profesor;

use App\Models\Profesor;
use Livewire\Component;

class MostrarProfesores extends Component
{

    public $filtrar_status;
    public $search;

    public $selected = [];
    public $selectAll = false;


    public function getProfesoresProperty()
    {

        $query = Profesor::orderBy('nombre', 'desc');

        if ($this->filtrar_status) {
            $query->where('status', $this->filtrar_status == 'Activo' ? 'true' : 'false');
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

        return $query->paginate(15);
    }


    public function render()
    {
        return view('livewire.admin.profesor.mostrar-profesores', [
            'profesores' => $this->profesores,
        ]);
    }
}
