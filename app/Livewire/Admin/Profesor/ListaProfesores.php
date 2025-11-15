<?php

namespace App\Livewire\Admin\Profesor;

use App\Models\Periodo;
use App\Models\Profesor;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ListaProfesores extends Component
{
    public $query = '';
    // public $profesores = [];
    public $selectedIndex = 0;
    public $selectedProfesor = null;

    public $materiasAsignadas = [];
    public $buscador_materia = '';

    public $periodo_id = '';

    // ===== Eventos de UI =====
    public function updatedQuery()
    {
        // Si limpian el select
        if (empty($this->query)) {
            $this->selectedProfesor = null;
            $this->materiasAsignadas = [];
            return;
        }

        // Buscar profesor con todas sus relaciones
        $profesor = Profesor::with(['user'])->find($this->query);

        if ($profesor) {
            $this->selectedProfesor = $profesor->toArray();
            $this->cargarMateriasAsignadas();
        } else {
            $this->selectedProfesor = null;
            $this->materiasAsignadas = [];
        }
    }


    // ===== Datos =====
    public function cargarMateriasAsignadas()
    {
        if (! $this->selectedProfesor) {
            $this->materiasAsignadas = [];
            return;
        }

        $profesorId = $this->selectedProfesor['id'];

        // Solo materias que tienen horario asignado
        $this->materiasAsignadas = DB::table('asignacion_materias as am')
            ->join('materias as m', 'am.materia_id', '=', 'm.id')
            ->join('modalidades as mo', 'am.modalidad_id', '=', 'mo.id')
            ->join('cuatrimestres as c', 'am.cuatrimestre_id', '=', 'c.id')
            ->join('licenciaturas as l', 'am.licenciatura_id', '=', 'l.id')
            ->join('profesores as p', 'am.profesor_id', '=', 'p.id')
            ->join('horarios as h', 'h.asignacion_materia_id', '=', 'am.id') // INNER JOIN => debe tener horario
            ->where('am.profesor_id', $profesorId)
            ->select([
                'am.id as asignacion_materia_id',
                'm.id as materia_id',
                'm.nombre as materia',
                'mo.id as modalidad_id',
                'mo.nombre as modalidad',
                'c.id as cuatrimestre_id',
                'c.cuatrimestre as cuatrimestre',
                'l.id as licenciatura_id',
                'l.nombre as licenciatura',
                DB::raw('GROUP_CONCAT(DISTINCT h.generacion_id) as generaciones')
            ])
            ->groupBy(
                'am.id', 'm.id', 'm.nombre',
                'mo.id', 'mo.nombre',
                'c.id', 'c.cuatrimestre',
                'l.id', 'l.nombre'
            )
            ->orderBy('mo.nombre')
            ->orderBy('c.cuatrimestre')
            ->get()
            ->toArray();
    }

    public function getMateriasFiltradasProperty()
    {
        if (! $this->selectedProfesor) {
            return collect(); // Nada hasta escoger profesor
        }

        $needle = mb_strtolower($this->buscador_materia);
        return collect($this->materiasAsignadas)->filter(function ($row) use ($needle) {
            return $needle === '' || str_contains(mb_strtolower($row->materia), $needle);
        })->values();
    }

    public function render()
    {
        $profesores = Profesor::with('user')
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->get()
            ->toArray();
        // Si usas periodos más adelante, deja esto. No se muestra hasta elegir profesor.
        return view('livewire.admin.profesor.lista-profesores', [
            'periodos' => Periodo::orderBy('id', 'desc')->get(),
            'profesores' => $profesores,
        ]);
    }
}
