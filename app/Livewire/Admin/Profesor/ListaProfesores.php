<?php

namespace App\Livewire\Admin\Profesor;

use App\Models\Periodo;
use App\Models\Profesor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ListaProfesores extends Component
{
    public string $query = '';

    public int $selectedIndex = 0;

    public ?array $selectedProfesor = null;

    public array $materiasAsignadas = [];

    public array $materiasSeleccionadas = [];

    public string $buscador_materia = '';

    public string $periodo_id = '';

    public function updatedQuery(): void
    {
        $this->seleccionarProfesor($this->query);
    }

    public function seleccionarProfesor($profesorId): void
    {
        $profesorId = (int) $profesorId;
        $this->query = $profesorId > 0 ? (string) $profesorId : '';
        $this->selectedIndex = 0;

        if ($profesorId <= 0) {
            $this->limpiarProfesorSeleccionado();

            return;
        }

        $profesor = Profesor::with('user')->find($profesorId);

        if (! $profesor) {
            $this->limpiarProfesorSeleccionado();

            return;
        }

        $this->selectedProfesor = $profesor->toArray();
        $this->buscador_materia = '';
        $this->cargarMateriasAsignadas();
    }

    public function limpiarProfesorSeleccionado(): void
    {
        $this->query = '';
        $this->selectedIndex = 0;
        $this->selectedProfesor = null;
        $this->materiasAsignadas = [];
        $this->materiasSeleccionadas = [];
        $this->buscador_materia = '';
        $this->periodo_id = '';
    }

    public function cargarMateriasAsignadas(): void
    {
        if (! $this->selectedProfesor || empty($this->selectedProfesor['id'])) {
            $this->materiasAsignadas = [];
            $this->materiasSeleccionadas = [];

            return;
        }

        $profesorId = (int) $this->selectedProfesor['id'];

        $this->materiasAsignadas = DB::table('asignacion_materias as am')
            ->join('materias as m', 'am.materia_id', '=', 'm.id')
            ->join('modalidades as mo', 'am.modalidad_id', '=', 'mo.id')
            ->join('cuatrimestres as c', 'am.cuatrimestre_id', '=', 'c.id')
            ->join('licenciaturas as l', 'am.licenciatura_id', '=', 'l.id')
            ->join('horarios as h', 'h.asignacion_materia_id', '=', 'am.id')
            ->leftJoin('generaciones as g', 'h.generacion_id', '=', 'g.id')
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
                DB::raw('GROUP_CONCAT(DISTINCT h.generacion_id ORDER BY h.generacion_id SEPARATOR ",") as generaciones'),
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT(h.generacion_id, '|', COALESCE(g.generacion, h.generacion_id)) ORDER BY h.generacion_id SEPARATOR ',') as generaciones_detalle"),
            ])
            ->groupBy(
                'am.id',
                'm.id',
                'm.nombre',
                'mo.id',
                'mo.nombre',
                'c.id',
                'c.cuatrimestre',
                'l.id',
                'l.nombre'
            )
            ->orderBy('l.nombre')
            ->orderBy('c.cuatrimestre')
            ->orderBy('m.nombre')
            ->get()
            ->toArray();

        $this->seleccionarTodasMaterias();
    }

    public function seleccionarTodasMaterias(): void
    {
        $this->materiasSeleccionadas = collect($this->materiasAsignadas)
            ->pluck('asignacion_materia_id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    public function limpiarSeleccionMaterias(): void
    {
        $this->materiasSeleccionadas = [];
    }

    public function getMateriasFiltradasProperty(): Collection
    {
        if (! $this->selectedProfesor) {
            return collect();
        }

        $needle = mb_strtolower(trim($this->buscador_materia));

        return collect($this->materiasAsignadas)
            ->filter(function ($row) use ($needle) {
                if ($needle === '') {
                    return true;
                }

                return str_contains(mb_strtolower($row->materia ?? ''), $needle)
                    || str_contains(mb_strtolower($row->modalidad ?? ''), $needle)
                    || str_contains(mb_strtolower($row->licenciatura ?? ''), $needle)
                    || str_contains((string) ($row->cuatrimestre ?? ''), $needle);
            })
            ->values();
    }

    public function getTotalLicenciaturasProperty(): int
    {
        return collect($this->materiasAsignadas)
            ->pluck('licenciatura_id')
            ->filter()
            ->unique()
            ->count();
    }

    public function getTotalGeneracionesProperty(): int
    {
        return collect($this->materiasAsignadas)
            ->flatMap(function ($row) {
                return array_filter(explode(',', (string) ($row->generaciones ?? '')));
            })
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->count();
    }

    public function getPeriodoEtiquetaProperty(): string
    {
        return match ($this->periodo_id) {
            '9-12' => 'SEP/DIC',
            '1-4' => 'ENE/ABR',
            '5-8' => 'MAY/AGO',
            default => 'Sin seleccionar',
        };
    }

    public function render()
    {
        $profesores = Profesor::with('user')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get()
            ->map(function ($profesor) {
                return [
                    'id' => $profesor->id,
                    'nombre' => $profesor->nombre,
                    'apellido_paterno' => $profesor->apellido_paterno,
                    'apellido_materno' => $profesor->apellido_materno,
                    'CURP' => $profesor->CURP ?? null,
                    'email' => $profesor->user->email ?? null,
                    'nombre_completo' => trim(
                        ($profesor->apellido_paterno ?? '').' '.
                        ($profesor->apellido_materno ?? '').' '.
                        ($profesor->nombre ?? '')
                    ),
                ];
            })
            ->values()
            ->toArray();

        return view('livewire.admin.profesor.lista-profesores', [
            'periodos' => Periodo::orderBy('id', 'desc')->get(),
            'profesores' => $profesores,
        ]);
    }
}
