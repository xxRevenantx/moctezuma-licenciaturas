<?php

namespace App\Livewire\Admin\Profesor;

use App\Models\Profesor;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ListaProfesores extends Component
{
    public $query = '';
    public $profesores = [];
    public $selectedIndex = 0;
    public $selectedProfesor = null;

    public $periodo_id = '';
    public $materiasAsignadas = [];

    public function updatedQuery()
    {
        $this->buscarProfesores();
    }

    public function buscarProfesores()
    {
        if (strlen($this->query) > 0) {
            $this->profesores = Profesor::with('user')
                ->where('nombre', 'like', '%' . $this->query . '%')
                ->orWhere('apellido_paterno', 'like', '%' . $this->query . '%')
                ->orWhere('apellido_materno', 'like', '%' . $this->query . '%')
                ->orWhereHas('user', function ($q) {
                    $q->where('CURP', 'like', '%' . $this->query . '%')
                        ->orWhere('email', 'like', '%' . $this->query . '%');
                })
                ->get()
                ->map(function ($profesor) {
                    return [
                        'id' => $profesor->id,
                        'nombre' => $profesor->nombre,
                        'apellido_paterno' => $profesor->apellido_paterno,
                        'apellido_materno' => $profesor->apellido_materno,
                        'CURP' => $profesor->user->CURP ?? '',
                    ];
                })
                ->toArray();
        } else {
            $this->profesores = [];
        }
        $this->selectedIndex = 0;
    }

    public function selectProfesor($index)
    {
        if (isset($this->profesores[$index])) {
            $this->selectedProfesor = $this->profesores[$index];
            $this->profesores = [];
            $this->cargarMateriasAsignadas();
        } else {
            $this->dispatch('swal', [
                'title' => 'Profesor no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);
        }
    }

    public function updatedPeriodoId()
    {
        $this->cargarMateriasAsignadas();
    }

    public function cargarMateriasAsignadas()
    {
        if ($this->selectedProfesor && $this->periodo_id) {
            $profesorId = $this->selectedProfesor['id'];
            $cuatrimestreId = $this->periodo_id;

            $this->materiasAsignadas = DB::table('asignacion_materias')
                ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
                ->select('materias.nombre')
                ->where('asignacion_materias.profesor_id', $profesorId)
                ->where('asignacion_materias.cuatrimestre_id', $cuatrimestreId)
                ->get()
                ->toArray();
        } else {
            $this->materiasAsignadas = [];
        }
    }

    public function selectIndexUp()
    {
        if ($this->profesores) {
            $this->selectedIndex = ($this->selectedIndex - 1 + count($this->profesores)) % count($this->profesores);
        }
    }

    public function selectIndexDown()
    {
        if ($this->profesores) {
            $this->selectedIndex = ($this->selectedIndex + 1) % count($this->profesores);
        }
    }

    public function render()
    {
        return view('livewire.admin.profesor.lista-profesores');
    }
}
