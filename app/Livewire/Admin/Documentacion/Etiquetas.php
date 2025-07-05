<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Inscripcion;
use Livewire\Component;

class Etiquetas extends Component
{

      public $query = '';
    public $alumnos = [];
    public $selectedIndex = 0;
    public $alumnosSeleccionados = []; // <= aquí cambia

    public function updatedQuery()
    {
        $this->buscarAlumnos();
    }

    public function buscarAlumnos()
    {
        if (strlen($this->query) > 0) {
            $this->alumnos = Inscripcion::with('licenciatura')
                ->where('nombre', 'like', '%' . $this->query . '%')
                ->orWhere('apellido_paterno', 'like', '%' . $this->query . '%')
                ->orWhere('apellido_materno', 'like', '%' . $this->query . '%')
                ->orWhere('curp', 'like', '%' . $this->query . '%')
                ->orWhere('matricula', 'like', '%' . $this->query . '%')
                ->get()
                ->toArray();
        } else {
            $this->alumnos = [];
        }

        $this->selectedIndex = 0;
    }

    public function selectAlumno($index)
    {
        if (isset($this->alumnos[$index])) {
            $alumno = $this->alumnos[$index];

            // Evitar duplicados
            if (!collect($this->alumnosSeleccionados)->contains('id', $alumno['id'])) {
                $this->alumnosSeleccionados[] = $alumno;
            }

            $this->query = ''; // limpiar el input
            $this->alumnos = [];
        } else {
            $this->dispatch('swal', [
                'title' => 'Alumno no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);
        }
    }

    public function eliminarAlumnoSeleccionado($id)
    {
        $this->alumnosSeleccionados = array_filter($this->alumnosSeleccionados, function ($a) use ($id) {
            return $a['id'] !== $id;
        });
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
        return view('livewire.admin.documentacion.etiquetas');
    }
}
