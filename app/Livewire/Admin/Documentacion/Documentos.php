<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Inscripcion;
use Livewire\Component;

class Documentos extends Component
{
    public $query = '';
    public $alumnos = [];
    public $selectedIndex = 0;
    public $selectedAlumno = null;

    public function updatedQuery()
    {
        $this->buscarAlumnos();
    }

    public function buscarAlumnos()
    {
        if (strlen($this->query) > 0) {
            $this->alumnos = Inscripcion::where('nombre', 'like', '%' . $this->query . '%')
                ->orWhere('apellido_paterno', 'like', '%' . $this->query . '%')
                ->orWhere('apellido_materno', 'like', '%' . $this->query . '%')
                ->orWhere('curp', 'like', '%' . $this->query . '%')
                ->orWhere('matricula', 'like', '%' . $this->query . '%')
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->alumnos = [];
        }
        $this->selectedIndex = 0;
    }

    public function selectAlumno($index)
    {
        $this->selectedAlumno = $this->alumnos[$index];
        $this->query = $this->selectedAlumno['apellido_paterno'] . ' ' . $this->selectedAlumno['apellido_materno'] . ' ' . $this->selectedAlumno['nombre'];
        $this->alumnos = [];
    }

    public function selectIndexUp()
    {
        if ($this->alumnos) {
            $this->selectedIndex = ($this->selectedIndex - 1 + count($this->alumnos)) % count($this->alumnos);
        }
    }

    public function selectIndexDown()
    {
        if ($this->alumnos) {
            $this->selectedIndex = ($this->selectedIndex + 1) % count($this->alumnos);
        }
    }

    public function render()
    {
        return view('livewire.admin.documentacion.documentos');
    }
}
