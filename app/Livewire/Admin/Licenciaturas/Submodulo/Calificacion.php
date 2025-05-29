<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Models\AsignacionMateria;
use App\Models\AsignarGeneracion;
use App\Models\Calificacion as ModelsCalificacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use Livewire\Attributes\On;
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
                ->orderBy('id', 'asc')
                ->get();

            $this->generacion_filtrada = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
                ->where('modalidad_id', $this->modalidad->id)
                ->where('generacion_id', $this->filtrar_generacion)
                ->first();

            $this->periodo = Periodo::where('generacion_id', $this->filtrar_generacion)
                ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                ->first();

             $this->reset('calificaciones');
            $this->dispatch('refreshComponente'); // <-- Esto recarga el componente

        }

        if ($propertyName === 'filtrar_cuatrimestre') {
            $this->periodo = Periodo::where('generacion_id', $this->filtrar_generacion)
                ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                ->first();
            $this->reset('calificaciones');
            $this->dispatch('refreshComponente'); // <-- Esto recarga el componente

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
    // Permitir calificación vacía ('' o null) o 0, además de 5-10 y 'NP'
    $valor_valido = false;

    if ($valor === '' || $valor === null) {
        $valor_valido = true; // Permitir guardar vacío
    } elseif (is_numeric($valor)) {
        $valor_num = floatval($valor);
        if ($valor_num == 0 || ($valor_num >= 5 && $valor_num <= 10)) {
            $valor_valido = true;
        }
    } elseif (strtoupper(trim($valor)) === 'NP') {
        $valor_valido = true;
    }

    if (!$valor_valido) {
        continue;
    }

    $materia_asignada = AsignacionMateria::find($materia_id);

    if ($materia_asignada) {
        $yaExiste = \App\Models\Calificacion::where('alumno_id', $alumno_id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', '!=', $this->filtrar_cuatrimestre)
            ->whereHas('asignacionMateria', function ($query) use ($materia_asignada) {
                $query->where('materia_id', $materia_asignada->materia_id);
            })
            ->exists();

        if ($yaExiste) {
            continue;
        }
    }

    $profesor_id = $materia_asignada ? $materia_asignada->profesor_id : null;

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

    $this->dispatch('swal', [
        'title' => 'Calificaciones guardadas',
        'icon' => 'success',
        'position' => 'top-end',
    ]);



}


#[On('refreshComponente')]
 public function render()
{
    $alumnos = collect();
    $materias = collect();
    $calificaciones_guardadas = collect();

    // Resetea calificaciones ANTES de poblar, para evitar "mezclar"
    $this->calificaciones = [];

    if ($this->filtrar_generacion && $this->filtrar_cuatrimestre) {
        $alumnos = Inscripcion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                    ->orWhere('matricula', 'like', '%' . $this->search . '%')
                    ;
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        $materias = AsignacionMateria::with(['materia', 'profesor'])
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->whereHas('materia') // Asegura que tenga relación con materia
            ->get()
            ->sortBy(function ($asignacion) {
            return optional($asignacion->materia)->clave;
            })
            ->values();

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

        // Llena $this->calificaciones con lo que hay en la base SOLO para ese cuatrimestre
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
