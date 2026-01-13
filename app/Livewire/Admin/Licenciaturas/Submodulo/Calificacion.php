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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;

class Calificacion extends Component
{
    public $modalidad;     // Modelo resuelto
    public $licenciatura;  // Modelo resuelto

    /** Filtros */
    public $generaciones = [];
    public $cuatrimestres = [];
    public $generacion_filtrada = null;
    public $filtrar_generacion = null;
    public $filtrar_cuatrimestre = null;

    /** Contexto */
    public $periodo = null;
    public $search = '';

    /** Tabla */
    public $alumnos = [];
    public $materias = [];       // asignacion_materia CANÓNICA por materia
    public $calificaciones = []; // [alumno_id][canon_id] => valor

    /** ✅ IMPORTANTE: en Livewire deben ser PUBLIC para persistir entre requests */
    public array $canonicoPorMateria = [];   // [materia_id] => asig_canónico_id
    public array $otrosIdsPorCanonico = [];  // [asig_canónico_id] => [otros_asig_id...]
    public array $idsParaLimpiar = [];       // ids de asignaciones del cuatri (incluye duplicadas)

    /** UI flags */
    public bool $todas_calificaciones_guardadas = false;
    public bool $hayCambios = false;
    public bool $yaExistenEnBD = false;

    /** ✅ modo desde el botón */
    public string $modoGuardar = 'guardar'; // 'guardar' | 'actualizar'

    /** ======================= CICLO DE VIDA ======================= */
    public function mount($modalidad, $licenciatura)
    {
        $this->licenciatura = Licenciatura::where('slug', $licenciatura)->firstOrFail();
        $this->modalidad    = Modalidad::where('slug', $modalidad)->firstOrFail();

        $this->generaciones = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->whereHas('generacion', fn($q) => $q->where('activa', "true"))
            ->with('generacion')
            ->get();

        $this->resetContexto();
    }

    /** ======================= UPDATERS (Livewire v3) ======================= */
    public function updatedFiltrarGeneracion($value): void
    {
        $this->filtrar_cuatrimestre = null;
        $this->search = '';
        $this->calificaciones = [];

        $this->cuatrimestres = [];
        $this->periodo = null;
        $this->alumnos = [];
        $this->materias = [];
        $this->yaExistenEnBD = false;

        $this->canonicoPorMateria = [];
        $this->otrosIdsPorCanonico = [];
        $this->idsParaLimpiar = [];

        if (!$value) return;

        $this->generacion_filtrada = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $value)
            ->with('generacion')
            ->first();

        $this->cuatrimestres = Periodo::with(['cuatrimestre', 'mes'])
            ->where('generacion_id', $value)
            ->orderBy('cuatrimestre_id')
            ->get()
            ->unique('cuatrimestre_id')
            ->values()
            ->all();

        $this->dispatch('refreshComponente');
    }

    public function updatedFiltrarCuatrimestre($value): void
    {
        $this->calificaciones = [];
        $this->search = '';

        if (!$this->filtrar_generacion || !$value) {
            $this->resetTabla();
            return;
        }

        $this->cargarDataset();
        $this->dispatch('refreshComponente');
    }

    public function updatedSearch(): void
    {
        if ($this->filtrar_generacion && $this->filtrar_cuatrimestre) {
            $this->cargarAlumnos();
            $this->hidratarInputsConBD();
        }
    }

    /**
     * ✅ ELIMINACIÓN INMEDIATA
     * Se ejecuta cuando cambia calificaciones.{alumnoId}.{canonId}
     */
    public function updatedCalificaciones($value, string $key): void
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) return;

        [$alumnoId, $canonId] = array_pad(explode('.', $key, 2), 2, null);
        $alumnoId = (int) $alumnoId;
        $canonId  = (int) $canonId;

        if (!$alumnoId || !$canonId) return;

        $v = $this->normalizarValor($value);

        // ✅ Solo eliminamos si quedó vacío
        if ($v !== null) return;

        $idsCanon = collect($this->materias)->pluck('id')->map(fn($x) => (int)$x)->all();
        if (!in_array($canonId, $idsCanon, true)) return;

        $deleted = ModelsCalificacion::query()
            ->where('alumno_id', $alumnoId)
            ->where('asignacion_materia_id', $canonId)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->delete();

        if ($deleted > 0) {
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Calificación eliminada',
                'text' => 'Se eliminó de la base de datos.',
                'position' => 'top-end',
                'timer' => 1400,
                'showConfirmButton' => false,
            ]);
        }

        $this->recalcularFlags();
    }

    /** ======================= ACCIONES UI ======================= */
    public function limpiarFiltros()
    {
        $this->filtrar_generacion = null;
        $this->filtrar_cuatrimestre = null;
        $this->resetContexto();
    }

    private function resetContexto(): void
    {
        $this->cuatrimestres = [];
        $this->generacion_filtrada = null;
        $this->periodo = null;
        $this->search = '';
        $this->resetTabla();
    }

    private function resetTabla(): void
    {
        $this->alumnos = [];
        $this->materias = [];
        $this->calificaciones = [];
        $this->canonicoPorMateria = [];
        $this->otrosIdsPorCanonico = [];
        $this->idsParaLimpiar = [];
        $this->todas_calificaciones_guardadas = false;
        $this->hayCambios = false;
        $this->yaExistenEnBD = false;
        $this->modoGuardar = 'guardar';
    }

    /** ======================= DATASET ======================= */
    private function cargarDataset(): void
    {
        $this->periodo = Periodo::with(['cuatrimestre', 'mes'])
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->first();

        $this->cargarMateriasCanonicas();
        $this->cargarAlumnos();
        $this->hidratarInputsConBD();
        $this->recalcularFlags();
    }

    /**
     * ✅ Si el request llega “nuevo” (click del botón),
     * y Livewire no trae dataset listo, lo reconstruimos.
     */
    private function asegurarDatasetMinimo(): void
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) return;

        // Si falta el mapa o ids para limpiar, reconstruye materias canónicas
        if (empty($this->idsParaLimpiar) || empty($this->canonicoPorMateria) || empty($this->materias)) {
            $this->cargarMateriasCanonicas();
        }

        if (empty($this->alumnos)) {
            $this->cargarAlumnos();
        }
    }

    private function cargarMateriasCanonicas(): void
    {
        $asignacionesAll = AsignacionMateria::with(['materia', 'profesor'])
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->whereHas('materia', fn($q) => $q->where('calificable', "true"))
            ->get();

        $this->idsParaLimpiar = $asignacionesAll->pluck('id')->values()->all();

        $grupos = $asignacionesAll->groupBy('materia_id');

        $canonicoPorMateria = [];
        $otrosIdsPorCanonico = [];

        foreach ($grupos as $materiaId => $grupo) {
            $canon = (int) $grupo->min('id');
            $canonicoPorMateria[$materiaId] = $canon;

            $otros = $grupo->pluck('id')
                ->reject(fn($id) => (int)$id === (int)$canon)
                ->values()
                ->all();

            $otrosIdsPorCanonico[$canon] = $otros;
        }

        $this->canonicoPorMateria = $canonicoPorMateria;
        $this->otrosIdsPorCanonico = $otrosIdsPorCanonico;

        $this->materias = $grupos
            ->map(fn($g) => $g->sortBy('id')->first()) // canónica por materia
            ->sortBy(fn($asig) => optional($asig->materia)->clave)
            ->values()
            ->all();
    }

    private function cargarAlumnos(): void
    {
        $query = Inscripcion::query()
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('status', 'true')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre');

        if (trim($this->search) !== '') {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                    ->orWhere('apellido_paterno', 'like', "%{$s}%")
                    ->orWhere('apellido_materno', 'like', "%{$s}%")
                    ->orWhere('matricula', 'like', "%{$s}%");
            });
        }

        $this->alumnos = $query->get()->all();
    }

    private function hidratarInputsConBD(): void
    {
        if (!count($this->alumnos) || !count($this->materias) || empty($this->idsParaLimpiar)) return;

        $alumnoIds = collect($this->alumnos)->pluck('id')->values()->all();

        $califs = ModelsCalificacion::with('asignacionMateria')
            ->whereIn('alumno_id', $alumnoIds)
            ->whereIn('asignacion_materia_id', $this->idsParaLimpiar)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->get();

        $guardadas = [];
        foreach ($califs as $c) {
            $materiaId = optional($c->asignacionMateria)->materia_id;
            if (!$materiaId) continue;

            $canonId = $this->canonicoPorMateria[$materiaId] ?? null;
            if (!$canonId) continue;

            // Si hay varias en BD, la última gana (solo para mostrar)
            $guardadas[$c->alumno_id][$canonId] = $c->calificacion;
        }

        foreach ($this->alumnos as $al) {
            foreach ($this->materias as $asigCanon) {
                $canonId = $asigCanon->id;
                $valorBD = $guardadas[$al->id][$canonId] ?? null;

                // ✅ si el usuario ya tocó el campo, NO lo sobrescribas
                if (
                    !isset($this->calificaciones[$al->id]) ||
                    !array_key_exists($canonId, $this->calificaciones[$al->id])
                ) {
                    $this->calificaciones[$al->id][$canonId] = $valorBD;
                }
            }
        }
    }

    private function recalcularFlags(): void
    {
        $this->todas_calificaciones_guardadas = false;
        $this->hayCambios = false;
        $this->yaExistenEnBD = false;

        if (!count($this->alumnos) || !count($this->materias) || empty($this->idsParaLimpiar)) return;

        $alumnoIds = collect($this->alumnos)->pluck('id')->values()->all();
        $idsCanon  = collect($this->materias)->pluck('id')->values()->all();

        $this->yaExistenEnBD = ModelsCalificacion::query()
            ->whereIn('alumno_id', $alumnoIds)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->whereIn('asignacion_materia_id', $this->idsParaLimpiar)
            ->exists();

        $bd = ModelsCalificacion::with('asignacionMateria')
            ->whereIn('alumno_id', $alumnoIds)
            ->whereIn('asignacion_materia_id', $this->idsParaLimpiar)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->get();

        $bdCanon = [];
        foreach ($bd as $c) {
            $materiaId = optional($c->asignacionMateria)->materia_id;
            if (!$materiaId) continue;

            $canonId = $this->canonicoPorMateria[$materiaId] ?? null;
            if (!$canonId) continue;

            $bdCanon[$c->alumno_id][$canonId] = $c->calificacion;
        }

        $todas = true;
        $hayCambios = false;

        foreach ($this->alumnos as $al) {
            foreach ($idsCanon as $canonId) {
                $vp = $this->normalizarValor($this->calificaciones[$al->id][$canonId] ?? null);
                $vb = $this->normalizarValor($bdCanon[$al->id][$canonId] ?? null);

                if (!$this->esValorValido($vp)) {
                    $todas = false;
                }

                if ($vp !== $vb) {
                    $hayCambios = true;
                }
            }
        }

        $this->todas_calificaciones_guardadas = $todas;
        $this->hayCambios = $hayCambios;
    }

    private function normalizarValor($valor): ?string
    {
        if ($valor === null) return null;

        $v = trim((string)$valor);
        if ($v === '' || $v === '0' || $v === '0.0') return null;

        if (strtoupper($v) === 'NP') return 'NP';

        if (is_numeric($v)) {
            return (string)((float)$v);
        }

        return strtoupper($v);
    }

    private function esValorValido(?string $v): bool
    {
        if ($v === null) return false;
        if ($v === 'NP') return true;

        if (is_numeric($v)) {
            $n = (float)$v;
            return $n >= 5 && $n <= 10;
        }
        return false;
    }

    /** ======================= GUARDAR / ACTUALIZAR ======================= */
    public function guardarTodasLasCalificaciones(): void
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Debes seleccionar generación y cuatrimestre.',
                'position' => 'top-end',
            ]);
            return;
        }

        // ✅ reconstruye dataset si el request llegó “nuevo”
        $this->asegurarDatasetMinimo();

        if (!count($this->alumnos) || !count($this->materias)) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'No hay alumnos o materias para guardar.',
                'position' => 'top-end',
            ]);
            return;
        }

        $alumnoIds = collect($this->alumnos)->pluck('id')->values()->all();
        $idsCanon  = collect($this->materias)->pluck('id')->values()->all();

        // Construir filas a insertar (válidas y no vacías)
        $rows = [];
        $invalidos = false;
        $now = now();

        $profesorPorCanon = collect($this->materias)->mapWithKeys(fn($a) => [$a->id => $a->profesor_id])->all();

        foreach ($alumnoIds as $alumnoId) {
            foreach ($idsCanon as $canonId) {
                $v = $this->normalizarValor($this->calificaciones[$alumnoId][$canonId] ?? null);

                if ($v === null) continue;

                if (!$this->esValorValido($v)) {
                    $invalidos = true;
                    break 2;
                }

                $rows[] = [
                    'alumno_id'             => $alumnoId,
                    'asignacion_materia_id' => $canonId, // ✅ SIEMPRE canónico
                    'modalidad_id'          => $this->modalidad->id,
                    'generacion_id'         => $this->filtrar_generacion,
                    'licenciatura_id'       => $this->licenciatura->id,
                    'cuatrimestre_id'       => $this->filtrar_cuatrimestre,
                    'profesor_id'           => $profesorPorCanon[$canonId] ?? null,
                    'calificacion'          => $v,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];
            }
        }

        if ($invalidos) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Existen calificaciones no válidas',
                'text' => 'Solo puedes ingresar valores entre 5 y 10 o "NP".',
                'position' => 'top-end',
            ]);
            return;
        }

        /**
         * ✅ ACTUALIZAR: borrar TODO el contexto (incluye duplicados) y reinsertar lo capturado
         * (vacíos quedan eliminados porque no se insertan)
         */
        if ($this->modoGuardar === 'actualizar') {
            DB::transaction(function () use ($alumnoIds, $rows) {
                // ✅ Con idsParaLimpiar ya persistido (public) SÍ borra
                ModelsCalificacion::query()
                    ->whereIn('alumno_id', $alumnoIds)
                    ->where('modalidad_id', $this->modalidad->id)
                    ->where('licenciatura_id', $this->licenciatura->id)
                    ->where('generacion_id', $this->filtrar_generacion)
                    ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                    ->whereIn('asignacion_materia_id', $this->idsParaLimpiar)
                    ->delete();

                if (!empty($rows)) {
                    ModelsCalificacion::insert($rows);
                }
            });

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Calificaciones actualizadas correctamente',
                'position' => 'top-end',
            ]);

            $this->recalcularFlags();
            return;
        }

        /**
         * ✅ GUARDAR: insertar solo NO duplicados
         * PERO detecta duplicado aunque en BD exista con asignacion_materia_id NO canónico.
         *
         * Estrategia:
         * - Traemos existentes del cuatrimestre usando idsParaLimpiar
         * - Los normalizamos a clave alumno|canonId (por materia_id)
         * - Si ya existe alumno|canonId, se omite
         */
        $existentes = ModelsCalificacion::with('asignacionMateria:id,materia_id')
            ->whereIn('alumno_id', $alumnoIds)
            ->whereIn('asignacion_materia_id', $this->idsParaLimpiar) // ✅ no solo $idsCanon
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->get();

        $setExistentesCanon = [];
        foreach ($existentes as $e) {
            $materiaId = $e->asignacionMateria->materia_id ?? null;
            if (!$materiaId) continue;

            $canonId = $this->canonicoPorMateria[$materiaId] ?? null;
            if (!$canonId) continue;

            $setExistentesCanon[$e->alumno_id . '|' . $canonId] = true;
        }

        $rowsNoDuplicados = [];
        $omitidos = 0;

        foreach ($rows as $r) {
            $keyCanon = $r['alumno_id'] . '|' . $r['asignacion_materia_id']; // asignacion_materia_id ya es canonId
            if (isset($setExistentesCanon[$keyCanon])) {
                $omitidos++;
                continue;
            }
            $rowsNoDuplicados[] = $r;
        }

        if (empty($rowsNoDuplicados)) {
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Sin cambios',
                'text' => 'Todo lo capturado ya existía (duplicados).',
                'position' => 'top-end',
            ]);
            $this->recalcularFlags();
            return;
        }

        try {
            ModelsCalificacion::insert($rowsNoDuplicados);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo guardar',
                'text'  => 'Ocurrió un error al insertar. Revisa duplicados/índices en la base de datos.',
                'position' => 'top-end',
            ]);
            return;
        }

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $omitidos > 0 ? 'Guardado parcial' : 'Guardado correcto',
            'text'  => $omitidos > 0
                ? ('Se guardaron ' . count($rowsNoDuplicados) . ' calificaciones. Se omitieron ' . $omitidos . ' por duplicado.')
                : 'Calificaciones guardadas correctamente.',
            'position' => 'top-end',
        ]);

        $this->recalcularFlags();
    }

    /** ======================= ENVÍOS ======================= */
    public function enviarCalificacion($alumnoId, $cuatrimestreId, $generacionId, $modalidadId)
    {
        $periodo = Periodo::with(['cuatrimestre', 'mes'])
            ->where('generacion_id', $generacionId)
            ->where('cuatrimestre_id', $cuatrimestreId)
            ->first();

        $inscripcion = Inscripcion::with('user')->find($alumnoId);
        if (!$inscripcion) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Alumno no encontrado', 'position' => 'top-end']);
            return;
        }

        $correo = $inscripcion->user->email ?? null;
        if (!$correo) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'El alumno no tiene correo registrado', 'position' => 'top-end']);
            return;
        }

        $calificaciones = ModelsCalificacion::with(['asignacionMateria.materia', 'asignacionMateria.profesor'])
            ->where('alumno_id', $alumnoId)
            ->where('modalidad_id', $modalidadId)
            ->where('licenciatura_id', $inscripcion->licenciatura_id)
            ->where('generacion_id', $generacionId)
            ->where('cuatrimestre_id', $cuatrimestreId)
            ->get()
            ->sortBy(fn($item) => $item->asignacionMateria->materia->clave ?? '')
            ->values();

        $escuela = Escuela::first();
        $licenciatura = Licenciatura::find($inscripcion->licenciatura_id);
        $generacionObj = Generacion::find($generacionId);
        $cuatrimestreObj = Cuatrimestre::find($cuatrimestreId);
        $ciclo_escolar = ModelsDashboard::latest()->first();

        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Enviando correo...',
            'position' => 'top',
        ]);

        Mail::to($correo)->queue(new CalificacionMail(
            $calificaciones,
            $escuela,
            $inscripcion,
            $licenciatura,
            $generacionObj,
            $cuatrimestreObj,
            $ciclo_escolar,
            $periodo
        ));

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Correo encolado correctamente.',
            'position' => 'top-end',
        ]);
    }

    public function enviarCalificacionesMasivas()
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Debes seleccionar generación y cuatrimestre.', 'position' => 'top-end']);
            return;
        }

        $alumnos = Inscripcion::with('user')
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->get();

        $periodo = Periodo::with(['cuatrimestre', 'mes'])
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->first();

        $escuela       = Escuela::first();
        $licenciatura  = $this->licenciatura;
        $generacionObj = Generacion::find($this->filtrar_generacion);
        $cuatriObj     = Cuatrimestre::find($this->filtrar_cuatrimestre);
        $ciclo_escolar = ModelsDashboard::latest()->first();

        foreach ($alumnos as $inscripcion) {
            $correo = $inscripcion->user->email ?? null;
            if (!$correo) continue;

            $calificaciones = ModelsCalificacion::with(['asignacionMateria.materia', 'asignacionMateria.profesor'])
                ->where('alumno_id', $inscripcion->id)
                ->where('modalidad_id', $this->modalidad->id)
                ->where('licenciatura_id', $licenciatura->id)
                ->where('generacion_id', $this->filtrar_generacion)
                ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                ->get()
                ->sortBy(fn($item) => $item->asignacionMateria->materia->clave ?? '')
                ->values();

            Mail::to($correo)->queue(new CalificacionMail(
                $calificaciones,
                $escuela,
                $inscripcion,
                $licenciatura,
                $generacionObj,
                $cuatriObj,
                $ciclo_escolar,
                $periodo
            ));
        }

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Todos los correos fueron encolados.',
            'position' => 'top-end',
        ]);
    }

    /** ======================= RENDER ======================= */
    #[On('refreshComponente')]
    public function render()
    {
        if ($this->filtrar_generacion && $this->filtrar_cuatrimestre && count($this->alumnos) && count($this->materias)) {
            $this->recalcularFlags();
        }

        return view('livewire.admin.licenciaturas.submodulo.calificacion', [
            'generaciones' => $this->generaciones,
            'cuatrimestres' => $this->cuatrimestres,
            'alumnos' => collect($this->alumnos),
            'materias' => collect($this->materias),
            'todas_calificaciones_guardadas' => $this->todas_calificaciones_guardadas,
            'hayCambios' => $this->hayCambios,
            'periodo' => $this->periodo,
            'generacion_filtrada' => $this->generacion_filtrada,
            'yaExistenEnBD' => $this->yaExistenEnBD,
        ]);
    }
}
