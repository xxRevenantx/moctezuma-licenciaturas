<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Models\AsignacionMateria;
use App\Models\AsignarGeneracion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use Livewire\Component;

class Calificacion extends Component
{
    public $modalidad;
    public $licenciatura;
    public $generaciones = [];
    public $cuatrimestres = [];
    public $calificaciones = [];
    public $generacion_filtrada;
    public $filtrar_generacion = null;
    public $filtrar_cuatrimestre = null;
    public $periodo = [];
    public $search = '';

    public function mount($modalidad, $licenciatura)
    {
        $this->licenciatura = Licenciatura::where('slug', $licenciatura)->firstOrFail();
        $this->modalidad = Modalidad::where('slug', $modalidad)->firstOrFail();

        $this->generaciones = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->whereHas('generacion', function ($query) {
                $query->where('activa', "true");
            })
            ->get();

        $this->cuatrimestres = [];
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'filtrar_generacion') {
            $this->filtrar_cuatrimestre = null;
            $this->cuatrimestres = Periodo::where('generacion_id', $this->filtrar_generacion)
                ->orderBy('id', 'desc')
                ->get();

            $this->generacion_filtrada = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
                ->where('modalidad_id', $this->modalidad->id)
                ->where('generacion_id', $this->filtrar_generacion)
                ->first();

            $this->periodo = Periodo::where('generacion_id', $this->filtrar_generacion)
                ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                ->first();
        }

        if ($propertyName === 'filtrar_cuatrimestre') {
            $this->periodo = Periodo::where('generacion_id', $this->filtrar_generacion)
                ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                ->first();
        }
    }

    public function limpiarFiltros()
    {
        $this->filtrar_generacion = null;
        $this->filtrar_cuatrimestre = null;
        $this->cuatrimestres = [];
        $this->search = '';
    }

    public function guardarCalificaciones()
{


    foreach ($this->calificaciones as $alumno_id => $materias) {
        foreach ($materias as $materia_id => $valor) {
            if ($valor !== '' && $valor !== null) {
                // Permitir solo valores entre 5 y 10 o "NP"
                $valor_valido = false;

                if (is_numeric($valor)) {
                    $valor_num = floatval($valor);
                    if ($valor_num >= 5 && $valor_num <= 10) {
                        $valor_valido = true;
                    }
                } elseif (strtoupper(trim($valor)) === 'NP') {
                    $valor_valido = true;
                }

                if (!$valor_valido) {
                    $this->dispatch('swal', [
                        'title' => 'La calificación debe ser un número entre 5 y 10 o "NP".',
                        'icon' => 'error',
                        'position' => 'top',
                    ]);
                    return;
                }

                // Busca el profesor vinculado en AsignacionMateria
                $asignacion = \App\Models\AsignacionMateria::where('id', $materia_id)
                    ->where('licenciatura_id', $this->licenciatura->id)
                    ->where('modalidad_id', $this->modalidad->id)
                    ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                    ->first();

                $profesor_id = $asignacion ? $asignacion->profesor_id : null;

                \App\Models\Calificacion::updateOrCreate(
                    [
                        'alumno_id' => $alumno_id,
                        'asignacion_materia_id' => $materia_id,
                        'modalidad_id' => $this->modalidad->id,
                        'licenciatura_id' => $this->licenciatura->id,
                        'generacion_id' => $this->filtrar_generacion,
                        'cuatrimestre_id' => $this->filtrar_cuatrimestre,
                    ],
                    [
                        'calificacion' => $valor,
                        'profesor_id'  => $profesor_id,
                    ]
                );
            }
        }
    }

    $this->dispatch('swal', [
        'title' => 'Calificaciones guardadas',
        'icon' => 'success',
        'position' => 'top-end',
    ]);
}

    public function render()
{
    $alumnos = collect();
    $materias = collect();
    $calificaciones_guardadas = collect();

    if ($this->filtrar_generacion && $this->filtrar_cuatrimestre) {
        $alumnos = Inscripcion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%');
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        $materias = AsignacionMateria::with('materia', 'profesor')
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->get();

        // Carga calificaciones guardadas para los alumnos y materias mostrados
        $alumno_ids = $alumnos->pluck('id')->toArray();
        $materia_ids = $materias->pluck('id')->toArray(); // id de AsignacionMateria

        $calificaciones_guardadas = \App\Models\Calificacion::whereIn('alumno_id', $alumno_ids)
            ->whereIn('asignacion_materia_id', $materia_ids)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->get();

        // Llena $this->calificaciones con lo que hay en la base
        foreach ($calificaciones_guardadas as $cali) {
            $this->calificaciones[$cali->alumno_id][$cali->asignacion_materia_id] = $cali->calificacion;
        }
    }

    return view('livewire.admin.licenciaturas.submodulo.calificacion', [
        'generaciones' => $this->generaciones,
        'cuatrimestres' => $this->cuatrimestres,
        'alumnos' => $alumnos,
        'materias' => $materias,
    ]);
}

}
