<?php

namespace App\Livewire\Admin\HorarioGeneral;

use App\Models\Horario;
use Livewire\Component;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class HorarioGeneralEscolarizada extends Component
{
    public $horarios;
    public $columnasUnicas;
    public $horasUnicas;
    public $busqueda = '';
    public $filtroLicenciatura = '';
    public $filtroCuatrimestre = '';
    public $filtroGeneracion = '';
    public $cuatrimestresDisponibles = [];
    public $generacionesDisponibles = [];
    public $modalidadId = 1;

    public function mount()
    {
        $this->horarios = collect();
        $this->columnasUnicas = collect();
        $this->horasUnicas = collect();
    }

    public function updatedBusqueda()
    {
        $this->actualizarDatos();
    }

    public function updatedFiltroLicenciatura()
    {
        $this->filtroGeneracion = '';
        $this->filtroCuatrimestre = '';
        $this->generacionesDisponibles = [];
        $this->cuatrimestresDisponibles = [];
        $this->horarios = collect();
        $this->columnasUnicas = collect();
        $this->horasUnicas = collect();

        if (!$this->filtroLicenciatura) return;

        $this->generacionesDisponibles = Horario::where('modalidad_id', $this->modalidadId)
            ->where('licenciatura_id', $this->filtroLicenciatura)
            ->with('generacion')
            ->get()
            ->pluck('generacion')
            ->unique('id')
            ->sortBy('generacion')
            ->values();
    }

    public function updatedFiltroGeneracion()
    {
        $this->filtroCuatrimestre = '';
        $this->cuatrimestresDisponibles = [];
        $this->horarios = collect();
        $this->columnasUnicas = collect();
        $this->horasUnicas = collect();

        if (!$this->filtroLicenciatura || !$this->filtroGeneracion) return;

        $this->cuatrimestresDisponibles = Horario::where('modalidad_id', $this->modalidadId)
            ->where('licenciatura_id', $this->filtroLicenciatura)
            ->where('generacion_id', $this->filtroGeneracion)
            ->pluck('cuatrimestre_id')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    public function updatedFiltroCuatrimestre()
    {
        if (!$this->filtroLicenciatura || !$this->filtroGeneracion || !$this->filtroCuatrimestre) {
            $this->horarios = collect();
            $this->columnasUnicas = collect();
            $this->horasUnicas = collect();
            return;
        }

        $this->actualizarDatos();
    }

    public function actualizarDatos()
    {
        if (!$this->filtroLicenciatura || !$this->filtroCuatrimestre || !$this->filtroGeneracion) {
            $this->horarios = collect();
            $this->columnasUnicas = collect();
            $this->horasUnicas = collect();
            return;
        }

        $query = Horario::with('asignacionMateria.materia', 'asignacionMateria.profesor', 'licenciatura', 'generacion')
            ->where('modalidad_id', $this->modalidadId)
            ->where('licenciatura_id', $this->filtroLicenciatura)
            ->where('cuatrimestre_id', $this->filtroCuatrimestre)
            ->where('generacion_id', $this->filtroGeneracion);

        if ($this->busqueda) {
            $query->where(function ($q) {
                $q->whereHas('asignacionMateria.profesor', function ($sub) {
                    $sub->where('nombre', 'like', '%' . $this->busqueda . '%');
                })->orWhereHas('asignacionMateria.materia', function ($sub) {
                    $sub->where('nombre', 'like', '%' . $this->busqueda . '%');
                });
            });
        }

        $this->horarios = $query->get();

        $this->columnasUnicas = $this->horarios
            ->pluck('dia_id')
            ->unique()
            ->sort()
            ->map(fn ($dia) => [
                'dia_id' => $dia,
                'etiqueta' => $this->nombreDia($dia)
            ])
            ->values();

        $this->horasUnicas = $this->horarios->pluck('hora')
            ->unique()
            ->sortBy(function ($hora) {
                $inicio = explode('-', $hora)[0];
                return Carbon::createFromFormat('g:ia', $inicio)->format('H:i');
            })
            ->values();
    }

    public function render()
    {
        return view('livewire.admin.horario-general.horario-general-escolarizada', [
            'horarios' => $this->horarios,
            'columnasUnicas' => $this->columnasUnicas,
            'horasUnicas' => $this->horasUnicas,
            'licenciaturasDisponibles' => Horario::where('modalidad_id', $this->modalidadId)->with('licenciatura')->get()->pluck('licenciatura')->unique('id')->values(),
            'cuatrimestresDisponibles' => $this->cuatrimestresDisponibles,
            'generacionesDisponibles' => $this->generacionesDisponibles,
            'modalidadId' => $this->modalidadId,
        ]);
    }

    private function nombreDia($id)
    {
        return match((int) $id) {
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
            default => 'Desconocido'
        };
    }
}
