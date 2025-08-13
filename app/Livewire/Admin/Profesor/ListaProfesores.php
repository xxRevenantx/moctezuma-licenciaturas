<?php

namespace App\Livewire\Admin\Profesor;

use App\Models\Periodo;
use App\Models\Profesor;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ListaProfesores extends Component
{
    public $query = '';
    public $profesores = [];
    public $selectedIndex = 0;
    public $selectedProfesor = null;

    public $materiasAsignadas = [];
    public $buscador_materia = '';

    public $periodo_id = '';

    // ===== Eventos de UI =====
    public function updatedQuery()
    {
        $this->buscarProfesores();
    }

    public function buscarProfesores()
    {
        if (strlen(trim($this->query)) === 0) {
            $this->profesores = [];
            $this->selectedIndex = 0;
            return;
        }

        $this->profesores = Profesor::with('user')
            ->where(DB::raw("CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno)"), 'like', '%' . $this->query . '%')
            ->orWhereHas('user', function ($q) {
                $q->where('CURP', 'like', '%' . $this->query . '%')
                  ->orWhere('email', 'like', '%' . $this->query . '%');
            })
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
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

        $this->selectedIndex = 0;
    }

    public function selectProfesor($index)
    {
        if (! isset($this->profesores[$index])) {
            $this->dispatch('swal', [
                'title' => 'Profesor no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);
            return;
        }

        $this->selectedProfesor = $this->profesores[$index];
        $this->profesores = [];
        $this->buscador_materia = ''; // limpiar búsqueda
        $this->cargarMateriasAsignadas();
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
        // Si usas periodos más adelante, deja esto. No se muestra hasta elegir profesor.
        return view('livewire.admin.profesor.lista-profesores', [
            'periodos' => Periodo::orderBy('id', 'desc')->get(),
        ]);
    }
}
