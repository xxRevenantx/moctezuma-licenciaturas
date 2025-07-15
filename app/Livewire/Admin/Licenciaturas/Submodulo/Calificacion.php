<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;


use App\Models\AsignacionMateria;
use App\Models\AsignarGeneracion;
use App\Models\Calificacion as ModelsCalificacion;
use App\Models\Cuatrimestre;
use App\Models\Dashboard as ModelsDashboard;
use App\Models\Escuela;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;

class Calificacion extends Component
{
    public $modalidad;
    public $licenciatura;
    public $generaciones = [];
    public $cuatrimestres = [];
    public $calificaciones = []; // [alumno_id][materia_id] => valor
    public $generacion_filtrada;
    public $filtrar_generacion = null;
    public $filtrar_cuatrimestre = null;
    public $periodo = [];
    public $search = '';

    public $todas_calificaciones_guardadas = false;
    public $hayCambios = false;



    public function enviarCalificacion($alumno, $cuatrimestre, $generacion, $modalidad){


        $periodo = Periodo::where('generacion_id', $generacion)
         ->where('cuatrimestre_id',$cuatrimestre)
         ->first();


        $calificaciones = ModelsCalificacion::with(['asignacionMateria.materia', 'asignacionMateria.profesor'])
            ->where('alumno_id', $alumno)
            ->whereHas('asignacionMateria', function ($query) use ($modalidad, $generacion, $cuatrimestre) {
            $query->where('modalidad_id', $modalidad)
                  ->where('generacion_id', $generacion)
                  ->where('cuatrimestre_id', $cuatrimestre);
            })
            ->get()
            ->sortBy(function ($item) {
            return $item->asignacionMateria->materia->clave ?? '';
            })
            ->values();

        $escuela = Escuela::all()->first();
        $inscripcion = Inscripcion::where('id', $alumno)->first();
        $licenciatura = Licenciatura::where('id', $inscripcion->licenciatura_id)->first();
        $generacion = Generacion::where('id', $generacion)->first();
        $cuatrimestre = Cuatrimestre::where('id', $cuatrimestre)->first();

        $ciclo_escolar = ModelsDashboard::orderBy('id', 'desc')->first();

       $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Enviando correo espere',
            'position' => 'top',
        ]);

        Mail::to('prueba@prueba.com')->send(new \App\Mail\CalificacionMail($calificaciones, $escuela, $inscripcion, $licenciatura, $generacion, $cuatrimestre, $ciclo_escolar));

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Correo enviado correctamente',
            'position' => 'top-end',
        ]);

    }


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
            ->orderBy('id', 'asc')->get();

        $this->generacion_filtrada = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->first();

        $this->periodo = Periodo::where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->first();

        $this->calificaciones = [];
        $this->dispatch('refreshComponente');
    }
    if ($propertyName === 'filtrar_cuatrimestre') {
        $this->periodo = Periodo::where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->first();
        $this->calificaciones = [];
        $this->dispatch('refreshComponente');
    }
}





    public function limpiarFiltros()
    {
        $this->filtrar_generacion = null;
        $this->filtrar_cuatrimestre = null;
        $this->cuatrimestres = [];
        $this->search = '';
        $this->calificaciones = [];
    }

    // GUARDAR CALIFICACION INDIVIDUAL (blur)
public function guardarTodasLasCalificaciones()
{
    $alumnos = Inscripcion::where('licenciatura_id', $this->licenciatura->id)
        ->where('modalidad_id', $this->modalidad->id)
        ->where('generacion_id', $this->filtrar_generacion)
        ->pluck('id')
        ->toArray();

    $materias = AsignacionMateria::where('licenciatura_id', $this->licenciatura->id)
        ->where('modalidad_id', $this->modalidad->id)
        ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
        ->pluck('id')
        ->toArray();

    $valores_invalidos = [];

    foreach ($alumnos as $alumno_id) {
        foreach ($materias as $materia_id) {
            $valor = $this->calificaciones[$alumno_id][$materia_id] ?? null;

            // Si el valor es null, vacío, o 0 (numérico o string "0"), solo eliminar y continuar
            if (
                $valor === null ||
                $valor === '' ||
                (is_numeric($valor) && (int)$valor === 0) ||
                (is_string($valor) && trim($valor) === '0')
            ) {
                ModelsCalificacion::where([
                    'alumno_id' => $alumno_id,
                    'asignacion_materia_id' => $materia_id,
                    'modalidad_id' => $this->modalidad->id,
                    'licenciatura_id' => $this->licenciatura->id,
                    'generacion_id' => $this->filtrar_generacion,
                    'cuatrimestre_id' => $this->filtrar_cuatrimestre,
                ])->delete();

                unset($this->calificaciones[$alumno_id][$materia_id]);
                continue;
            }

            // Solo números 5-10 o NP, si no, es inválido
            if (!is_numeric($valor) && strtoupper(trim($valor)) !== 'NP') {
                $valores_invalidos[] = $valor;
                continue;
            }
            if (is_numeric($valor) && ($valor < 5 || $valor > 10)) {
                $valores_invalidos[] = $valor;
                continue;
            }
        }
    }

    // Si hay valores inválidos, muestra swal de error y no guarda nada
    if (!empty($valores_invalidos)) {
        $this->dispatch('swal', [
            'icon' => 'error',
            'title' => 'Existen calificaciones no válidas',
            'text' => 'Solo puedes ingresar valores entre 5 y 10 o "NP". Corrige los valores e intenta guardar de nuevo.',
            'position' => 'top-end',
        ]);
        return;
    }

    // Si no hay inválidos, ahora sí guarda
    foreach ($alumnos as $alumno_id) {
        foreach ($materias as $materia_id) {
            $valor = $this->calificaciones[$alumno_id][$materia_id] ?? null;

            if (
                $valor === null ||
                $valor === '' ||
                (is_numeric($valor) && (int)$valor === 0) ||
                (is_string($valor) && trim($valor) === '0')
            ) {
                ModelsCalificacion::where([
                    'alumno_id' => $alumno_id,
                    'asignacion_materia_id' => $materia_id,
                    'modalidad_id' => $this->modalidad->id,
                    'licenciatura_id' => $this->licenciatura->id,
                    'generacion_id' => $this->filtrar_generacion,
                    'cuatrimestre_id' => $this->filtrar_cuatrimestre,
                ])->delete();

                unset($this->calificaciones[$alumno_id][$materia_id]);
                continue;
            }

            // Solo guarda valores válidos (ya validado antes)
            $materia_asignada = AsignacionMateria::find($materia_id);
            if (!$materia_asignada) continue;

            ModelsCalificacion::updateOrCreate(
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
                    'profesor_id' => $materia_asignada->profesor_id,
                ]
            );
        }
    }

    $this->dispatch('swal', [
        'icon' => 'success',
        'title' => 'Calificaciones guardadas correctamente',
        'position' => 'top-end',
    ]);
}









#[On('refreshComponente')]
public function render()
{
    $alumnos = collect();
    $materias = collect();
    $this->hayCambios = false; // Inicializa aquí para no arrastrar valor viejo

    if ($this->filtrar_generacion && $this->filtrar_cuatrimestre) {
        $alumnos = Inscripcion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                    ->orWhere('matricula', 'like', '%' . $this->search . '%');
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        $materias = AsignacionMateria::with(['materia', 'profesor'])
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->whereHas('materia')
            ->get()
            ->sortBy(fn($asignacion) => optional($asignacion->materia)->clave)
            ->values();

        $alumno_ids = $alumnos->pluck('id')->toArray();
        $materia_ids = $materias->pluck('id')->toArray();

        // NO REINICIES $this->calificaciones
        $calificaciones_guardadas = ModelsCalificacion::whereIn('alumno_id', $alumno_ids)
            ->whereIn('asignacion_materia_id', $materia_ids)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->get()
            ->groupBy('alumno_id');

        // Actualiza array de calificaciones con lo de la BD
       foreach ($alumnos as $alumno) {
                foreach ($materias as $materia) {
                    $cali_guardada = isset($calificaciones_guardadas[$alumno->id])
                        ? $calificaciones_guardadas[$alumno->id]->firstWhere('asignacion_materia_id', $materia->id)
                        : null;
                    $this->calificaciones[$alumno->id][$materia->id] = $this->calificaciones[$alumno->id][$materia->id] ?? ($cali_guardada ? $cali_guardada->calificacion : null);
                }
            }


        // Determina si ya todas las calificaciones han sido guardadas
        $todas = true;
        foreach ($alumnos as $alumno) {
            foreach ($materias as $materia) {
                $valor = $this->calificaciones[$alumno->id][$materia->id] ?? null;
                if ($valor === null || $valor === '' || (is_numeric($valor) && ($valor < 5 || $valor > 10)) || (!is_numeric($valor) && strtoupper(trim($valor)) !== 'NP')) {
                    $todas = false;
                    break 2;
                }
            }
        }
        $this->todas_calificaciones_guardadas = $todas;

        // Detección de cambios (habilita/deshabilita botón)
        $hayCambios = false;
        foreach ($alumnos as $alumno) {
            foreach ($materias as $materia) {
                $valor_pantalla = $this->calificaciones[$alumno->id][$materia->id] ?? null;
                $cali_guardada = isset($calificaciones_guardadas[$alumno->id])
                    ? $calificaciones_guardadas[$alumno->id]->firstWhere('asignacion_materia_id', $materia->id)
                    : null;
$valor_bd = $cali_guardada ? $cali_guardada->calificacion : null;

                // Si difiere (incluyendo nulos/vacíos), hay cambios
                if ($valor_pantalla !== $valor_bd) {
                    $hayCambios = true;
                    break 2;
                }
            }
        }
        $this->hayCambios = $hayCambios;
    }

    return view('livewire.admin.licenciaturas.submodulo.calificacion', [
        'generaciones' => $this->generaciones,
        'cuatrimestres' => $this->cuatrimestres,
        'alumnos' => $alumnos,
        'materias' => $materias,
        'todas_calificaciones_guardadas' => $this->todas_calificaciones_guardadas,
        'hayCambios' => $this->hayCambios,
    ]);
}




}
