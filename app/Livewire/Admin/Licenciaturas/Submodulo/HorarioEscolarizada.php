<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;


use App\Models\AsignacionMateria;
use App\Models\AsignarGeneracion;
use App\Models\dia;
use App\Models\Generacion;
use App\Models\Horario;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\Profesor;
use Livewire\Attributes\On;
use Livewire\Component;

class HorarioEscolarizada extends Component
{

    public $modalidad;
    public $licenciatura;


    public $horasDisponibles = [];
    public $horarios = [];
    public $hora;

    public $dias = [];
    public $dia_id;
    public $materias = [];
    public $asignacion_materia_id;


    public $filtrar_generacion;
    public $filtrar_cuatrimestre;
    public $generaciones;
    public $cuatrimestres = [];

    public $horario = [];



public function cargarHorario()
{
    // Solo si ambos están seleccionados
    if ($this->filtrar_generacion && $this->filtrar_cuatrimestre) {
        $horariosBD = Horario::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->get();

        // Llena el array sólo con las materias asignadas
        $this->horario = [];
        foreach ($horariosBD as $h) {
            $this->horario[$h->dia_id][$h->hora] = $h->asignacion_materia_id ? (string)$h->asignacion_materia_id : "0";
        }
    } else {
        $this->horario = [];
    }

    // Llena explícitamente los valores no asignados
    foreach ($this->dias as $dia) {
        foreach ($this->horasDisponibles as $hora) {
            if (!isset($this->horario[$dia->id][$hora])) {
                $this->horario[$dia->id][$hora] = "0";
            }
        }
    }
}






    public function mount($modalidad, $licenciatura)
    {
        $this->licenciatura = Licenciatura::where('slug', $licenciatura)->firstOrFail();
        $this->modalidad = Modalidad::where('slug', $modalidad)->firstOrFail();

        $this->generaciones = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->get();

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


        $this->dias = Dia::all();
        $this->materias = AsignacionMateria::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->get();


    }

    public function updated($propertyName)
        {
            // Si se actualiza la generación
            if ($propertyName === 'filtrar_generacion') {

                // Trae las generaciones activas (puede que esto sobre, revisa tu lógica)
                $this->generaciones = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
                    ->where('modalidad_id', $this->modalidad->id)
                    ->whereHas('generacion', function ($query) {
                        $query->where('activa', "true");
                    })

                    ->get();

                // Trae los cuatrimestres disponibles para la generación seleccionada
                $this->cuatrimestres = Periodo::where('generacion_id', $this->filtrar_generacion)
                    ->orderBy('id', 'desc')
                    ->limit(1)
                    ->get();

                // Limpia el cuatrimestre seleccionado anterior si cambia la generación
                $this->filtrar_cuatrimestre = null;
                $this->materias = [];
                return;
            }

            // Si se actualiza el cuatrimestre, Y ya hay generación seleccionada
            if ($propertyName === 'filtrar_cuatrimestre' && $this->filtrar_generacion) {

                // Trae las materias dependiendo de la licenciatura, modalidad, generación y cuatrimestre
                $this->materias = AsignacionMateria::where('licenciatura_id', $this->licenciatura->id)
                    ->where('modalidad_id', $this->modalidad->id)
                    ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                    ->get();
            $this->cargarHorario(); // <--- LLAMA AQUÍ
            }
        }



    //LIMPIAR FILTROS
    public function limpiarFiltros()
    {
        $this->filtrar_generacion = null;
        $this->filtrar_cuatrimestre = null;
    }




    // Guardar Horario
public function actualizarHorario($dia_id, $hora, $materia_id)
{
    // Si materia_id es vacío o 0, se asigna null
    $materia_id = empty($materia_id) || $materia_id == 0 ? null : $materia_id;



    // VERIFICA QUE NO EXISTA UN HORARIO CON LA MISMA HORA Y DÍA
    $existeHorario = Horario::where('licenciatura_id', $this->licenciatura->id)
        ->where('modalidad_id', $this->modalidad->id)
        ->where('generacion_id', $this->filtrar_generacion)
        ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
        ->where('asignacion_materia_id', $materia_id)
        ->where('dia_id', $dia_id)
        ->where('hora', $hora)
        ->exists();
    if ($existeHorario && $materia_id !== null) {
        // Si ya existe un horario con la misma hora y día, no se actualiza
        $this->dispatch('swal', [
            'icon' => 'error',
            'title' => 'Ya existe la materia con la misma hora y día.',
            'position' => 'top',
        ]);
        return;
    }

    // Actualiza si la materia está en la misma licenciatura, modalidad, generacion, cuatrimestre, dia y hora o crea el horario
    $horario = Horario::updateOrCreate(
        [
            'licenciatura_id' => $this->licenciatura->id,
            'modalidad_id' => $this->modalidad->id,
            'generacion_id' => $this->filtrar_generacion,
            'cuatrimestre_id' => $this->filtrar_cuatrimestre,
            'dia_id' => $dia_id,
            'hora' => $hora,
        ],
        [
            'asignacion_materia_id' => $materia_id,
        ]
    );

    // Limpia el horario para que se actualice la vista
    $this->horario = [];


    if($materia_id == null) {
        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Materia no asignada.',
            'position' => 'top',
        ]);
    } else {
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Horario actualizado correctamente.',
            'position' => 'top',
        ]);
    }
}




    #[On('refresh-tabla')]
    public function render()
    {

     $horarios = collect();
        if ($this->licenciatura->id && $this->modalidad->id) {
            $horarios = Horario::where('licenciatura_id', $this->licenciatura->id)
                ->where('modalidad_id', $this->modalidad->id)
                ->get();
        }


        return view('livewire.admin.licenciaturas.submodulo.horario-escolarizada', [
            'horarios' => $horarios,
            'dias' => $this->dias,
            'horas' => $this->horasDisponibles,
            'materias' => $this->materias,
            'generaciones' => $this->generaciones,

        ]);
    }
}
