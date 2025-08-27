<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Mail\CalificacionMail;
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
    public $calificaciones = []; // [alumno_id][asignacion_materia_id_canónico] => valor
    public $generacion_filtrada;
    public $filtrar_generacion = null;
    public $filtrar_cuatrimestre = null;
    public $periodo = [];
    public $search = '';

    public $todas_calificaciones_guardadas = false;
    public $hayCambios = false;

    /** ======================= ENVÍOS ======================= */
    public function enviarCalificacion($alumno, $cuatrimestre, $generacion, $modalidad)
    {
        $periodo = Periodo::where('generacion_id', $generacion)
            ->where('cuatrimestre_id', $cuatrimestre)
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

        $escuela = Escuela::first();
        $inscripcion = Inscripcion::where('id', $alumno)->first();
        $licenciatura = Licenciatura::where('id', $inscripcion->licenciatura_id)->first();
        $generacionObj = Generacion::where('id', $generacion)->first();
        $cuatrimestreObj = Cuatrimestre::where('id', $cuatrimestre)->first();

        $ciclo_escolar = ModelsDashboard::orderBy('id', 'desc')->first();

        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Enviando correo espere',
            'position' => 'top',
        ]);

        $correo = $inscripcion->user->email ?? null;
        if (empty($correo)) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'El alumno no tiene correo registrado',
                'position' => 'top-end',
            ]);
            return;
        }

        Mail::to("prueba@prueba.com")->send(new CalificacionMail(
            $calificaciones, $escuela, $inscripcion, $licenciatura, $generacionObj, $cuatrimestreObj, $ciclo_escolar, $periodo
        ));

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Correo enviado correctamente.',
            'position' => 'top-end',
        ]);
    }

    public function enviarCalificacionesMasivas()
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Debes seleccionar generación y cuatrimestre.',
                'position' => 'top-end',
            ]);
            return;
        }

        $alumnos = Inscripcion::with('user')
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->get();

        $periodo = Periodo::where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->first();

        $escuela       = Escuela::first();
        $licenciatura  = $this->licenciatura;
        $generacionObj = Generacion::find($this->filtrar_generacion);
        $cuatriObj     = Cuatrimestre::find($this->filtrar_cuatrimestre);
        $ciclo_escolar = ModelsDashboard::latest()->first();
        $modalidad     = $this->modalidad->id;

        foreach ($alumnos as $inscripcion) {
            $correo = $inscripcion->user->email ?? null;
            if (!$correo) continue;

            $calificaciones = ModelsCalificacion::with(['asignacionMateria.materia', 'asignacionMateria.profesor'])
                ->where('alumno_id', $inscripcion->id)
                ->whereHas('asignacionMateria', function ($query) use ($modalidad) {
                    $query->where('modalidad_id', $modalidad)
                          ->where('generacion_id', $this->filtrar_generacion)
                          ->where('cuatrimestre_id', $this->filtrar_cuatrimestre);
                })
                ->get()
                ->sortBy(function ($item) {
                    return $item->asignacionMateria->materia->clave ?? '';
                })
                ->values();

            Mail::to($correo)->queue(new CalificacionMail(
                $calificaciones, $escuela, $inscripcion, $licenciatura, $generacionObj, $cuatriObj, $ciclo_escolar, $periodo
            ));
        }

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Todos los correos fueron encolados.',
            'position' => 'top-end',
        ]);
    }

    /** ======================= CICLO DE VIDA ======================= */
    public function mount($modalidad, $licenciatura)
    {
        $this->licenciatura = Licenciatura::where('slug', $licenciatura)->firstOrFail();
        $this->modalidad    = Modalidad::where('slug', $modalidad)->firstOrFail();

        $this->generaciones = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->whereHas('generacion', fn($q) => $q->where('activa', "true"))
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

    /** ======================= GUARDADO ======================= */
    public function guardarTodasLasCalificaciones()
    {
        // Alumnos de este contexto
        $alumnos = Inscripcion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->pluck('id')->toArray();

        // Todas las asignaciones del contexto (calificables), agrupadas por materia
        $asignacionesAll = AsignacionMateria::with('materia')
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id',    $this->modalidad->id)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->whereHas('materia', fn($q) => $q->where('calificable', "true"))
            ->get()
            ->groupBy('materia_id');

        // ID canónico por materia (el menor id de cada grupo) e IDs a usar para columnas/guardado
        $canonicoPorMateria = $asignacionesAll->map(fn($g) => $g->min('id'));
        $materiasCanonIds   = $canonicoPorMateria->values()->all();

        // Mapa de "otros ids" por canónico para limpiar duplicados al guardar
        $otrosIdsPorCanonico = [];
        foreach ($asignacionesAll as $materiaId => $grupo) {
            $canon = $canonicoPorMateria[$materiaId];
            $otros = $grupo->pluck('id')->reject(fn($id) => $id == $canon)->values()->all();
            $otrosIdsPorCanonico[$canon] = $otros;
        }

        // Validación previa
        $valores_invalidos = [];
        foreach ($alumnos as $alumno_id) {
            foreach ($materiasCanonIds as $materia_id_canon) {
                $valor = $this->calificaciones[$alumno_id][$materia_id_canon] ?? null;

                if (
                    $valor === null ||
                    $valor === '' ||
                    (is_numeric($valor) && (float)$valor == 0.0) ||
                    (is_string($valor) && trim($valor) === '0')
                ) {
                    // Borrar en el id canónico
                    ModelsCalificacion::where([
                        'alumno_id'              => $alumno_id,
                        'asignacion_materia_id'  => $materia_id_canon,
                        'modalidad_id'           => $this->modalidad->id,
                        'licenciatura_id'        => $this->licenciatura->id,
                        'generacion_id'          => $this->filtrar_generacion,
                        'cuatrimestre_id'        => $this->filtrar_cuatrimestre,
                    ])->delete();

                    // Limpia posibles duplicados en otros ids de esa materia
                    if (!empty($otrosIdsPorCanonico[$materia_id_canon])) {
                        ModelsCalificacion::where('alumno_id', $alumno_id)
                            ->whereIn('asignacion_materia_id', $otrosIdsPorCanonico[$materia_id_canon])
                            ->where('modalidad_id',    $this->modalidad->id)
                            ->where('licenciatura_id', $this->licenciatura->id)
                            ->where('generacion_id',   $this->filtrar_generacion)
                            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                            ->delete();
                    }

                    unset($this->calificaciones[$alumno_id][$materia_id_canon]);
                    continue;
                }

                if (!is_numeric($valor) && strtoupper(trim($valor)) !== 'NP') {
                    $valores_invalidos[] = $valor;
                    continue;
                }
                if (is_numeric($valor) && ((float)$valor < 5 || (float)$valor > 10)) {
                    $valores_invalidos[] = $valor;
                    continue;
                }
            }
        }

        if (!empty($valores_invalidos)) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Existen calificaciones no válidas',
                'text' => 'Solo puedes ingresar valores entre 5 y 10 o "NP". Corrige los valores e intenta guardar de nuevo.',
                'position' => 'top-end',
            ]);
            return;
        }

        // Guardado con id canónico (y limpieza de duplicados)
        foreach ($alumnos as $alumno_id) {
            foreach ($materiasCanonIds as $materia_id_canon) {
                $valor = $this->calificaciones[$alumno_id][$materia_id_canon] ?? null;

                if (
                    $valor === null ||
                    $valor === '' ||
                    (is_numeric($valor) && (float)$valor == 0.0) ||
                    (is_string($valor) && trim($valor) === '0')
                ) {
                    ModelsCalificacion::where([
                        'alumno_id'              => $alumno_id,
                        'asignacion_materia_id'  => $materia_id_canon,
                        'modalidad_id'           => $this->modalidad->id,
                        'licenciatura_id'        => $this->licenciatura->id,
                        'generacion_id'          => $this->filtrar_generacion,
                        'cuatrimestre_id'        => $this->filtrar_cuatrimestre,
                    ])->delete();

                    if (!empty($otrosIdsPorCanonico[$materia_id_canon])) {
                        ModelsCalificacion::where('alumno_id', $alumno_id)
                            ->whereIn('asignacion_materia_id', $otrosIdsPorCanonico[$materia_id_canon])
                            ->where('modalidad_id',    $this->modalidad->id)
                            ->where('licenciatura_id', $this->licenciatura->id)
                            ->where('generacion_id',   $this->filtrar_generacion)
                            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                            ->delete();
                    }

                    unset($this->calificaciones[$alumno_id][$materia_id_canon]);
                    continue;
                }

                $materia_asignada = AsignacionMateria::find($materia_id_canon);
                if (!$materia_asignada) continue;

                ModelsCalificacion::updateOrCreate(
                    [
                        'alumno_id'             => $alumno_id,
                        'asignacion_materia_id' => $materia_id_canon,
                        'modalidad_id'          => $this->modalidad->id,
                        'licenciatura_id'       => $this->licenciatura->id,
                        'generacion_id'         => $this->filtrar_generacion,
                        'cuatrimestre_id'       => $this->filtrar_cuatrimestre,
                    ],
                    [
                        'calificacion' => $valor,
                        'profesor_id'  => $materia_asignada->profesor_id,
                    ]
                );

                // Borra calificaciones en otros ids de esa materia (si existieran)
                if (!empty($otrosIdsPorCanonico[$materia_id_canon])) {
                    ModelsCalificacion::where('alumno_id', $alumno_id)
                        ->whereIn('asignacion_materia_id', $otrosIdsPorCanonico[$materia_id_canon])
                        ->where('modalidad_id',    $this->modalidad->id)
                        ->where('licenciatura_id', $this->licenciatura->id)
                        ->where('generacion_id',   $this->filtrar_generacion)
                        ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                        ->delete();
                }
            }
        }

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Calificaciones guardadas correctamente',
            'position' => 'top-end',
        ]);
    }

    /** ======================= RENDER ======================= */
    #[On('refreshComponente')]
    public function render()
    {
        $alumnos = collect();
        $materias = collect();
        $this->hayCambios = false;

        if ($this->filtrar_generacion && $this->filtrar_cuatrimestre) {
            // Alumnos
            $alumnos = Inscripcion::where('licenciatura_id', $this->licenciatura->id)
                ->where('modalidad_id', $this->modalidad->id)
                ->where('generacion_id', $this->filtrar_generacion)
                ->where('status', 'true')
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

            /** ===== Asignaciones (todas), grupos por materia y columnas únicas ===== */
            $asignacionesAll = AsignacionMateria::with(['materia','profesor'])
                ->where('licenciatura_id', $this->licenciatura->id)
                ->where('modalidad_id',    $this->modalidad->id)
                ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                ->whereHas('materia', fn($q) => $q->where('calificable', "true"))
                ->get();

            // Grupos por materia y canónico (menor id)
            $gruposPorMateria   = $asignacionesAll->groupBy('materia_id');
            $canonicoPorMateria = $gruposPorMateria->map(fn($g) => $g->min('id'));

            // Columnas únicas: la fila canónica de cada materia
            $materias = $gruposPorMateria
                ->map(fn($g) => $g->sortBy('id')->first())
                ->sortBy(fn($asig) => optional($asig->materia)->clave)
                ->values();

            // IDs
            $idsCanonicos      = $canonicoPorMateria->values()->all();            // los que se muestran
            $idsParaConsulta   = $asignacionesAll->pluck('id')->values()->all();  // todos (para leer califs viejas)

            // ===== Calificaciones guardadas (normalizadas al id canónico) =====
            $califs = ModelsCalificacion::with('asignacionMateria')
                ->whereIn('alumno_id', $alumnos->pluck('id'))
                ->whereIn('asignacion_materia_id', $idsParaConsulta)
                ->where('modalidad_id',    $this->modalidad->id)
                ->where('licenciatura_id', $this->licenciatura->id)
                ->where('generacion_id',   $this->filtrar_generacion)
                ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                ->get();

            // Mapa [alumno_id][id_canónico] = calificación
            $calificaciones_guardadas = [];
            foreach ($califs as $c) {
                $materiaId = optional($c->asignacionMateria)->materia_id;
                if (!$materiaId) continue;
                $idCanon = $canonicoPorMateria[$materiaId] ?? null;
                if (!$idCanon) continue;
                $calificaciones_guardadas[$c->alumno_id][$idCanon] = $c->calificacion;
            }

            // Pinta valores (sin romper lo que el usuario ya tecleó)
            foreach ($alumnos as $alumno) {
                foreach ($materias as $materia) {
                    $valorGuardado = $calificaciones_guardadas[$alumno->id][$materia->id] ?? null;
                    $this->calificaciones[$alumno->id][$materia->id] =
                        $this->calificaciones[$alumno->id][$materia->id] ?? $valorGuardado;
                }
            }

            // ¿Todas válidas (5–10 o NP)?
            $todas = true;
            foreach ($alumnos as $alumno) {
                foreach ($materias as $materia) {
                    $valor = $this->calificaciones[$alumno->id][$materia->id] ?? null;
                    $esNP  = is_string($valor) && strtoupper(trim($valor)) === 'NP';
                    $okNum = is_numeric($valor) && (float)$valor >= 5 && (float)$valor <= 10;
                    if (!($esNP || $okNum)) { $todas = false; break 2; }
                }
            }
            $this->todas_calificaciones_guardadas = $todas;

            // Detección de cambios (normalizada a string/NP)
            $hayCambios = false;
            foreach ($alumnos as $alumno) {
                foreach ($materias as $materia) {
                    $valorPantalla = $this->calificaciones[$alumno->id][$materia->id] ?? null;
                    $valorBD       = $calificaciones_guardadas[$alumno->id][$materia->id] ?? null;

                    $vp = is_null($valorPantalla) ? null : (is_numeric($valorPantalla) ? (string)(float)$valorPantalla : strtoupper(trim((string)$valorPantalla)));
                    $vb = is_null($valorBD)       ? null : (is_numeric($valorBD)       ? (string)(float)$valorBD       : strtoupper(trim((string)$valorBD)));

                    if ($vp !== $vb) { $hayCambios = true; break 2; }
                }
            }
            $this->hayCambios = $hayCambios;
        }

        return view('livewire.admin.licenciaturas.submodulo.calificacion', [
            'generaciones' => $this->generaciones,
            'cuatrimestres' => $this->cuatrimestres,
            'alumnos' => $alumnos,
            'materias' => $materias, // <- columnas únicas (id canónico)
            'todas_calificaciones_guardadas' => $this->todas_calificaciones_guardadas,
            'hayCambios' => $this->hayCambios,
        ]);
    }
}
