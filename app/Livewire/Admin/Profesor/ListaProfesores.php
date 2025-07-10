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

    public $buscador_materia = '';

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
    if ($this->selectedProfesor) {
        $profesorId = $this->selectedProfesor['id'];

        $this->materiasAsignadas = DB::table('horarios')
            ->join('asignacion_materias', 'horarios.asignacion_materia_id', '=', 'asignacion_materias.id')
            ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
            ->join('modalidades', 'asignacion_materias.modalidad_id', '=', 'modalidades.id')
            ->join('cuatrimestres', 'asignacion_materias.cuatrimestre_id', '=', 'cuatrimestres.id')
            ->join('licenciaturas', 'asignacion_materias.licenciatura_id', '=', 'licenciaturas.id')
            ->select(
                'materias.id as materia_id',
                'materias.nombre as materia',
                'modalidades.nombre as modalidad',
                'cuatrimestres.cuatrimestre as cuatrimestre',
                'licenciaturas.nombre as licenciatura',
                'licenciaturas.id as licenciatura_id',
                'horarios.generacion_id as generacion_id',
                'horarios.modalidad_id as modalidad_id'
            )
            ->where('asignacion_materias.profesor_id', $profesorId)
            ->groupBy(
                'materias.id',
                'materias.nombre',
                'modalidades.nombre',
                'cuatrimestres.cuatrimestre',
                'licenciaturas.id',
                'licenciaturas.nombre',
                'horarios.generacion_id',
                'horarios.modalidad_id'
            )
            ->orderBy('modalidades.nombre')
            ->orderBy('cuatrimestres.cuatrimestre')
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

    public function getMateriasFiltradasProperty()
{
    return collect($this->materiasAsignadas)->filter(function ($materia) {
        return str_contains(
            strtolower($materia->materia),
            strtolower($this->buscador_materia)
        );
    })->values();
}



    public function render()
    {
        return view('livewire.admin.profesor.lista-profesores');
    }
}
