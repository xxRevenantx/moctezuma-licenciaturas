<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Models\AsignacionMateria;
use App\Models\AsignarGeneracion;
use App\Models\Dia;
use App\Models\Horario;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use Livewire\Component;

class HorarioSemiescolarizada extends Component
{
       public $modalidad;
    public $licenciatura;

    public $horasDisponibles = [];
    public $dias = [];
    public $materias = [];
    public $horario = [];

    public $filtrar_generacion = null;
    public $filtrar_cuatrimestre = null;
    public $generaciones = [];
    public $cuatrimestres = [];

    public function mount($modalidad, $licenciatura)
    {
        $this->licenciatura = Licenciatura::where('slug', $licenciatura)->firstOrFail();
        $this->modalidad = Modalidad::where('slug', $modalidad)->firstOrFail();

        $this->generaciones = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->WhereHas('generacion', function ($query) {
                $query->where('activa', "true");
            })
            ->get();

        $this->cuatrimestres = [];
        $this->horasDisponibles = [
            "8:00am-9:00am",
            "9:00am-10:00am",
            "10:00am-10:30am",
            "10:30am-11:30am",
            "11:30am-12:30pm",
            "12:30pm-1:30pm",
            "1:30pm-2:30pm",
            "2:30pm-3:30pm",
        ];
        $this->dias = Dia::where('dia', 'sábado')->get();
        $this->materias = [];
        $this->llenarHorarioEnBlanco();
    }

    public function llenarHorarioEnBlanco()
    {
        // Llena todos los selects en blanco ("0")
        $this->horario = [];
        foreach ($this->dias as $dia) {
            foreach ($this->horasDisponibles as $hora) {
                $this->horario[$dia->id][$hora] = "0";
            }
        }
    }

    public function cargarHorario()
{
    $this->llenarHorarioEnBlanco();
    if ($this->filtrar_generacion && $this->filtrar_cuatrimestre) {
        $horariosBD = Horario::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->get();
        foreach ($horariosBD as $h) {
            $this->horario[$h->dia_id][$h->hora] = (string) $h->asignacion_materia_id ?? "0";
        }
    }
}


    // Limpiar todo al hacer clic en el botón de filtros
    public function limpiarFiltros()
    {
        $this->filtrar_generacion = null;
        $this->filtrar_cuatrimestre = null;
        $this->materias = [];
        $this->horario = [];
        $this->cuatrimestres = [];
        $this->llenarHorarioEnBlanco();
    }

    // Guardar/actualizar materia para ese día/hora
    public function actualizarHorario($dia_id, $hora, $materia_id)
    {
        // Si materia_id es vacío o "0", se elimina el registro
        $materia_id = empty($materia_id) || $materia_id == 0 ? null : $materia_id;

        $criteria = [
            'licenciatura_id' => $this->licenciatura->id,
            'modalidad_id' => $this->modalidad->id,
            'generacion_id' => $this->filtrar_generacion,
            'cuatrimestre_id' => $this->filtrar_cuatrimestre,
            'dia_id' => $dia_id,
            'hora' => $hora,
        ];

        if (is_null($materia_id)) {
            Horario::where($criteria)->delete();
        } else {
            Horario::updateOrCreate(
                $criteria,
                ['asignacion_materia_id' => $materia_id]
            );
        }

        // Refresca solo la celda modificada
        $this->cargarHorario();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Horario actualizado correctamente',
            'position' => 'top-end',
        ]);
    }

    public function updated($propertyName)
    {
        // Cuando se cambia generación
        if ($propertyName === 'filtrar_generacion') {
            $this->cuatrimestres = Periodo::where('generacion_id', $this->filtrar_generacion)
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get();

            $this->filtrar_cuatrimestre = null;
            $this->materias = [];
            $this->horario = [];
            $this->llenarHorarioEnBlanco();
            return;
        }

        // Si se limpia el cuatrimestre, resetea materias y horario
        if ($propertyName === 'filtrar_cuatrimestre' && empty($this->filtrar_cuatrimestre)) {
            $this->materias = [];
            $this->horario = [];
            $this->llenarHorarioEnBlanco();
            return;
        }

        // Cuando se cambia cuatrimestre y ambos están presentes
        if (
            ($propertyName === 'filtrar_generacion' || $propertyName === 'filtrar_cuatrimestre')
            && $this->filtrar_generacion && $this->filtrar_cuatrimestre
        ) {
            $this->materias = AsignacionMateria::with(['materia', 'profesor'])
                ->where('licenciatura_id', $this->licenciatura->id)
                ->where('modalidad_id', $this->modalidad->id)
                ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                ->get();
            $this->cargarHorario();
        }
    }


    public function render()
    {
        return view('livewire.admin.licenciaturas.submodulo.horario-semiescolarizada',[
            'dias' => $this->dias,
            'horas' => $this->horasDisponibles,
            'materias' => $this->materias,
            'generaciones' => $this->generaciones,
            'cuatrimestres' => $this->cuatrimestres,
        ]);
    }
}
