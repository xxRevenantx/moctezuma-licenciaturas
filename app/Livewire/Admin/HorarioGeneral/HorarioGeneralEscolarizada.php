<?php

namespace App\Livewire\Admin\HorarioGeneral;

use App\Models\AsignacionMateria;
use App\Models\Dia;
use App\Models\Horario;
use Livewire\Component;
use Livewire\Attributes\On;

class HorarioGeneralEscolarizada extends Component
{
    public $licenciaturas = [];
    public $generaciones = [];
    public $cuatrimestres = [];

    public $licenciatura_id = null;
    public $filtrar_generacion   = null;
    public $filtrar_cuatrimestre = null;
    public $busqueda = '';

    public $modalidadId = 1;

    public $horas = [];
    public $dias = [];
    public $materias = [];   // opciones del <select>
    public $horario = [];    // matriz [dia_id][hora] = asignacion_materia_id

    public function mount()
    {
        // Catálogo de licenciaturas disponibles en horario (modalidad escolarizada = 1)
        $this->licenciaturas = Horario::where('modalidad_id', 1)
            ->with('licenciatura')
            ->get()
            ->pluck('licenciatura')
            ->unique('id')
            ->values();

        $this->horas = [
            "8:00am-9:00am","9:00am-10:00am","10:00am-10:30am",
            "10:30am-11:30am","11:30am-12:30pm","12:30pm-1:30pm",
            "1:30pm-2:30pm","2:30pm-3:30pm",
        ];

        $this->dias = Dia::where('dia', '!=', 'Sábado')->orderBy('id')->get();
        $this->materias = collect();
        $this->llenarHorarioEnBlanco();
    }

    private function llenarHorarioEnBlanco(): void
    {
        $this->horario = [];
        foreach ($this->dias as $dia) {
            foreach ($this->horas as $hora) {
                $this->horario[$dia->id][$hora] = "0";
            }
        }
    }

    /** ===== REACCIONES A LOS SELECTS ===== */

    public function updatedLicenciaturaId(): void
    {
        $this->reset(['filtrar_generacion','filtrar_cuatrimestre']);
        $this->generaciones = collect();
        $this->cuatrimestres = [];
        $this->materias = collect();
        $this->llenarHorarioEnBlanco();

        if (!$this->licenciatura_id) return;

        $this->generaciones = Horario::where('modalidad_id', $this->modalidadId)
            ->where('licenciatura_id', $this->licenciatura_id)
            ->with('generacion')
            ->get()
            ->pluck('generacion')
            ->filter()
            ->unique('id')
            ->sortBy('generacion')
            ->values();

        $this->intentarCargarHorario();
    }

    public function updatedFiltrarGeneracion(): void
    {
        $this->reset(['filtrar_cuatrimestre']);
        $this->cuatrimestres = [];
        $this->materias = collect();
        $this->llenarHorarioEnBlanco();

        if (!$this->licenciatura_id || !$this->filtrar_generacion) return;

        $this->cuatrimestres = Horario::where('modalidad_id', $this->modalidadId)
            ->where('licenciatura_id', $this->licenciatura_id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->pluck('cuatrimestre_id')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $this->intentarCargarHorario();
    }

    public function updatedFiltrarCuatrimestre(): void
    {
        $this->materias = collect();
        $this->llenarHorarioEnBlanco();

        $this->intentarCargarHorario();
    }

    /** ===== BUSCADOR ===== */

    public function updatedBusqueda(): void
    {
        // Solo recargar si ya están seleccionados los 3 filtros
        if ($this->licenciatura_id && $this->filtrar_generacion && $this->filtrar_cuatrimestre) {
            $this->cargarHorario();
        }
    }

    /** ===== CARGAS PRINCIPALES ===== */

    private function intentarCargarHorario(): void
    {
        if ($this->licenciatura_id && $this->filtrar_generacion && $this->filtrar_cuatrimestre) {
            $this->cargarMaterias();   // dejamos la lista completa para edición
            $this->cargarHorario();    // la búsqueda se aplica aquí
        }
    }

    private function cargarMaterias(): void
    {
        $this->materias = AsignacionMateria::with(['materia','profesor'])
            ->where('licenciatura_id', $this->licenciatura_id)
            ->where('modalidad_id', $this->modalidadId)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->get();
    }

    public function cargarHorario(): void
    {
        $this->llenarHorarioEnBlanco();

        if (!$this->licenciatura_id || !$this->filtrar_generacion || !$this->filtrar_cuatrimestre) {
            return;
        }

        $term = trim((string)$this->busqueda);

        $horariosBD = Horario::with(['asignacionMateria.materia','asignacionMateria.profesor'])
            ->where('licenciatura_id', $this->licenciatura_id)
            ->where('modalidad_id', $this->modalidadId)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($sub) use ($term) {
                    // Materia: nombre o clave
                    $sub->whereHas('asignacionMateria.materia', function ($mq) use ($term) {
                        $mq->where('nombre', 'like', "%{$term}%")
                           ->orWhere('clave', 'like', "%{$term}%");
                    })
                    // Profesor: nombre o apellidos
                    ->orWhereHas('asignacionMateria.profesor', function ($pq) use ($term) {
                        $pq->where('nombre', 'like', "%{$term}%")
                           ->orWhere('apellido_paterno', 'like', "%{$term}%")
                           ->orWhere('apellido_materno', 'like', "%{$term}%");
                    });
                });
            })
            ->get();

        foreach ($horariosBD as $h) {
            $this->horario[$h->dia_id][$h->hora] = (string)($h->asignacion_materia_id ?? "0");
        }
    }

    /** ===== EDITAR CELDAS ===== */

    public function actualizarHorario($dia_id, $hora, $materia_id): void
    {
        $materia_id = (empty($materia_id) || $materia_id == "0") ? null : $materia_id;

        $criteria = [
            'licenciatura_id' => $this->licenciatura_id,
            'modalidad_id'    => $this->modalidadId,
            'generacion_id'   => $this->filtrar_generacion,
            'cuatrimestre_id' => $this->filtrar_cuatrimestre,
            'dia_id'          => $dia_id,
            'hora'            => $hora,
        ];

        if (is_null($materia_id)) {
            Horario::where($criteria)->delete();
        } else {
            Horario::updateOrCreate($criteria, ['asignacion_materia_id' => $materia_id]);
        }

        $this->cargarHorario();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Horario actualizado correctamente',
            'position' => 'top-end',
        ]);
    }

    #[On('refresh-tabla')]
    public function render()
    {
        return view('livewire.admin.horario-general.horario-general-escolarizada', [
            'licenciaturas' => $this->licenciaturas,
            'generaciones'  => $this->generaciones,
            'cuatrimestres' => $this->cuatrimestres,
            'dias'          => $this->dias,
            'horas'         => $this->horas,
            'materias'      => $this->materias,
            'modalidadId'   => $this->modalidadId,
            'horario'       => $this->horario,
        ]);
    }
}
