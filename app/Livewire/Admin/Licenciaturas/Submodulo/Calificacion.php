<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Mail\CalificacionMail;
use App\Models\AsignacionMateria;
use App\Models\AsignarGeneracion;
use App\Models\CalificacionAuditoria;
use App\Models\Calificacion as ModelsCalificacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Services\Boletas\BoletaService;
use App\Services\Academico\AnalizadorTrayectoriaAcademica;
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
    public string $filtrar_origen = 'todos'; // todos | local | foraneo
    public string $filtroAcademico = 'todos'; // todos | pendientes | riesgo | completos
    public string $filtroPredictivo = 'todos'; // todos | alto | medio | bajo | sin_datos
    public array $alertasHistoricas = [];
    public ?int $alumnoPronosticoId = null;
    public string $nombreAlumnoPronostico = '';
    public array $pronosticoSeleccionado = [];
    public $generacionCargada = null;
    public $cuatrimestreCargado = null;
    public string $busquedaAplicada = '';

    /** Contexto */
    public $periodo = null;
    public $search = '';

    /** Tabla */
    public $alumnos = [];
    public $materias = [];       // asignacion_materia CANÓNICA por materia
    public $calificaciones = []; // [alumno_id][canon_id] => valor
    public array $notasPersistidas = []; // instantánea para detectar cambios sin consultar MySQL en cada tecla
    public ?int $alumnoHistorialId = null;
    public string $nombreAlumnoHistorial = '';
    public array $historialAlumnoNotas = [];

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
        if ($this->impedirCambioConPendientes()) {
            $this->filtrar_generacion = $this->generacionCargada;
            return;
        }
        $this->generacionCargada = $value ?: null;
        $this->cuatrimestreCargado = null;
        $this->busquedaAplicada = '';
        $this->filtroAcademico = 'todos';
        $this->filtroPredictivo = 'todos';
        $this->filtrar_cuatrimestre = null;
        $this->search = '';
        $this->calificaciones = [];

        $this->cuatrimestres = [];
        $this->periodo = null;
        $this->alumnos = [];
        $this->materias = [];
        $this->yaExistenEnBD = false;
        $this->hayCambios = false;
        $this->todas_calificaciones_guardadas = false;

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
        if ($this->impedirCambioConPendientes()) {
            $this->filtrar_cuatrimestre = $this->cuatrimestreCargado;
            return;
        }
        $this->cuatrimestreCargado = $value ?: null;
        $this->filtroAcademico = 'todos';
        $this->filtroPredictivo = 'todos';
        $this->busquedaAplicada = '';
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
        if ($this->impedirCambioConPendientes()) {
            $this->search = $this->busquedaAplicada;
            return;
        }
        $this->busquedaAplicada = $this->search;
        if ($this->filtrar_generacion && $this->filtrar_cuatrimestre) {
            $this->calificaciones = [];
            $this->cargarAlumnos();
            $this->hidratarInputsConBD();
            $this->recalcularFlags();
        }
    }

    /**
     * Impide descartar notas sin guardar al cambiar de contexto o filtro.
     * Se pide guardar primero en lugar de borrar silenciosamente la captura.
     */
    private function impedirCambioConPendientes(): bool
    {
        if (!$this->hayCambios) return false;

        $this->dispatch('swal', [
            'icon' => 'warning',
            'title' => 'Hay calificaciones sin guardar',
            'text' => 'Guarda o actualiza las calificaciones antes de cambiar de filtro.',
            'position' => 'top-end',
        ]);
        return true;
    }

    public function cambiarOrigen(string $origen): void
    {
        if (!in_array($origen, ['todos', 'local', 'foraneo'], true)) return;
        if ($origen === $this->filtrar_origen || $this->impedirCambioConPendientes()) return;

        $this->filtrar_origen = $origen;
        $this->calificaciones = [];
        if ($this->filtrar_generacion && $this->filtrar_cuatrimestre) {
            $this->cargarAlumnos();
            $this->hidratarInputsConBD();
            $this->recalcularFlags();
        }
    }

    /** Estado académico del conjunto guardado, estable durante la edición. */
    public function cambiarFiltroAcademico(string $estado): void
    {
        if (!in_array($estado, ['todos', 'pendientes', 'riesgo', 'completos'], true)) return;
        if ($estado === $this->filtroAcademico || $this->impedirCambioConPendientes()) return;
        $this->filtroAcademico = $estado;
    }

    /** El filtro predictivo usa notas PERSISTIDAS de periodos anteriores. */
    public function cambiarFiltroPredictivo(string $nivel): void
    {
        if (!in_array($nivel, ['todos', 'alto', 'medio', 'bajo', 'sin_datos'], true)) return;
        if ($nivel === $this->filtroPredictivo || $this->impedirCambioConPendientes()) return;
        $this->filtroPredictivo = $nivel;
    }

    /**
     * Captura diferida: limpiar una celda no borra registros de inmediato.
     * El modo Actualizar confirma la sustitución de notas solo de alumnos visibles.
     */
    public function updatedCalificaciones($value, string $key): void
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) return;
        [$alumnoId, $canonId] = array_pad(explode('.', $key, 2), 2, null);
        $alumnoId = (int) $alumnoId;
        $canonId = (int) $canonId;
        if (!$alumnoId || !$canonId) return;

        $visible = collect($this->alumnos)->contains(fn ($al) => (int) $al->id === $alumnoId);
        $materiaValida = collect($this->materias)->contains(fn ($mat) => (int) $mat->id === $canonId);
        if (!$visible || !$materiaValida) return;
        $this->recalcularFlags();
    }

    /** ======================= ACCIONES UI ======================= */
    public function limpiarFiltros()
    {
        if ($this->impedirCambioConPendientes()) return;
        $this->filtrar_origen = 'todos';
        $this->filtroAcademico = 'todos';
        $this->filtroPredictivo = 'todos';
        $this->generacionCargada = null;
        $this->cuatrimestreCargado = null;
        $this->busquedaAplicada = '';
        $this->filtrar_generacion = null;
        $this->filtrar_cuatrimestre = null;
        $this->resetContexto();
    }

    public function descartarCambios(): void
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) return;
        $this->calificaciones = [];
        $this->cargarAlumnos();
        $this->hidratarInputsConBD();
        $this->recalcularFlags();
        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Edición descartada',
            'text' => 'Se restauraron las calificaciones guardadas en la base de datos.',
            'position' => 'top-end',
        ]);
    }

    private function resetContexto(): void
    {
        $this->cuatrimestres = [];
        $this->generacion_filtrada = null;
        $this->periodo = null;
        $this->search = '';
        $this->busquedaAplicada = '';
        $this->resetTabla();
    }

    private function resetTabla(): void
    {
        $this->alumnos = [];
        $this->materias = [];
        $this->calificaciones = [];
        $this->notasPersistidas = [];
        $this->alertasHistoricas = [];
        $this->filtroPredictivo = 'todos';
        $this->cerrarPronosticoAlumno();
        $this->canonicoPorMateria = [];
        $this->otrosIdsPorCanonico = [];
        $this->idsParaLimpiar = [];
        $this->todas_calificaciones_guardadas = false;
        $this->hayCambios = false;
        $this->yaExistenEnBD = false;
        $this->modoGuardar = 'guardar';
        $this->cerrarHistorialAlumno();
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

        if ($this->filtrar_origen === 'local') {
            $query->where('foraneo', 'false');
        } elseif ($this->filtrar_origen === 'foraneo') {
            $query->where('foraneo', 'true');
        }

        if (trim($this->search) !== '') {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                    ->orWhere('apellido_paterno', 'like', "%{$s}%")
                    ->orWhere('apellido_materno', 'like', "%{$s}%")
                    ->orWhere('matricula', 'like', "%{$s}%")
                    ->orWhere('matricula_interna', 'like', "%{$s}%");
            });
        }

        $this->alumnos = $query->get()->all();
        // Se recalcula al cambiar generación, cuatrimestre, origen o búsqueda,
        // NUNCA por cada tecla editada en una calificación.
        $this->alertasHistoricas = ($this->filtrar_generacion && $this->filtrar_cuatrimestre)
            ? app(AnalizadorTrayectoriaAcademica::class)->analizar(
                collect($this->alumnos)->pluck('id')->all(),
                (int) $this->licenciatura->id,
                (int) $this->filtrar_generacion,
                (int) $this->filtrar_cuatrimestre
            ) : [];
        $this->cerrarPronosticoAlumno();
    }

    /** Hidrata notas una sola vez por contexto. Conserva la edición aún pendiente. */
    private function hidratarInputsConBD(): void
    {
        $this->notasPersistidas = [];
        if (!count($this->alumnos) || !count($this->materias) || empty($this->idsParaLimpiar)) return;

        $alumnoIds = collect($this->alumnos)->pluck('id')->values()->all();
        $califs = ModelsCalificacion::with('asignacionMateria:id,materia_id')
            ->whereIn('alumno_id', $alumnoIds)
            ->whereIn('asignacion_materia_id', $this->idsParaLimpiar)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->orderBy('id')
            ->get();

        foreach ($califs as $c) {
            $materiaId = $c->asignacionMateria?->materia_id;
            $canonId = $this->canonicoPorMateria[$materiaId] ?? null;
            if ($canonId) {
                $this->notasPersistidas[$c->alumno_id][$canonId] = $this->normalizarValor($c->calificacion);
            }
        }

        foreach ($this->alumnos as $al) {
            foreach ($this->materias as $m) {
                $valorBD = $this->notasPersistidas[$al->id][$m->id] ?? null;
                if (!array_key_exists($m->id, $this->calificaciones[$al->id] ?? [])) {
                    $this->calificaciones[$al->id][$m->id] = $valorBD;
                }
            }
        }
    }

    /** No consulta la BD en cada render/tecla. Los guardados validan de nuevo en servidor. */
    private function recalcularFlags(): void
    {
        $this->hayCambios = false;
        $this->yaExistenEnBD = false;
        $this->todas_calificaciones_guardadas = false;
        if (!count($this->alumnos) || !count($this->materias)) return;

        $todas = true;
        foreach ($this->alumnos as $al) {
            foreach ($this->materias as $m) {
                $valor = $this->normalizarValor($this->calificaciones[$al->id][$m->id] ?? null);
                $anterior = $this->normalizarValor($this->notasPersistidas[$al->id][$m->id] ?? null);
                if ($valor !== $anterior) $this->hayCambios = true;
                if (!$this->esValorValido($valor)) $todas = false;
                if ($anterior !== null) $this->yaExistenEnBD = true;
            }
        }
        $this->todas_calificaciones_guardadas = $todas;
    }

    /** NP impide promedio; los valores pendientes no se interpretan como cero. */
    private function diagnosticoAlumno(int $alumnoId, array $notas): array
    {
        $capturadas = $pendientes = $reprobadas = $np = $numericCount = 0;
        $suma = 0;
        $total = count($this->materias);

        foreach ($this->materias as $m) {
            $valor = $this->normalizarValor($notas[$alumnoId][$m->id] ?? null);
            if (!$this->esValorValido($valor)) {
                $pendientes++;
                continue;
            }
            $capturadas++;
            if ($valor === 'NP') {
                $np++;
                continue;
            }
            $numericCount++;
            $numero = (float) $valor;
            $suma += $numero;
            if ($numero < 6) $reprobadas++;
        }

        return [
            'total' => $total,
            'capturadas' => $capturadas,
            'pendientes' => $pendientes,
            'reprobadas' => $reprobadas,
            'np' => $np,
            'completo' => $total > 0 && $pendientes === 0,
            'riesgo' => $np > 0 || $reprobadas > 0,
            'promedio' => $np === 0 && $numericCount ? round($suma / $numericCount, 2) : null,
            'porcentaje' => $total ? (int) round($capturadas * 100 / $total) : 0,
        ];
    }

    /** El filtro académico consulta las notas GUARDADAS, para que una fila no
     * desaparezca mientras se escribe. Al guardar, se actualiza la lista. */
    private function coincideFiltroAcademico(int $alumnoId): bool
    {
        if ($this->filtroAcademico === 'todos') return true;
        $estado = $this->diagnosticoAlumno($alumnoId, $this->notasPersistidas);
        return match ($this->filtroAcademico) {
            'pendientes' => $estado['pendientes'] > 0,
            'riesgo' => $estado['riesgo'],
            'completos' => $estado['completo'],
            default => true,
        };
    }

    private function idsAlumnosVisibles(): array
    {
        return collect($this->alumnos)
            ->filter(fn ($al) => $this->coincideFiltroAcademico((int) $al->id)
                && ($this->filtroPredictivo === 'todos'
                    || ($this->alertasHistoricas[$al->id]['nivel'] ?? 'sin_datos') === $this->filtroPredictivo))
            ->pluck('id')->map(fn ($id) => (int) $id)->all();
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

    /** Auditoría compartida con Calificaciones por docente, sin nuevas tablas. */
    private function auditarCambio(
        ?ModelsCalificacion $registro,
        int $alumnoId,
        int $canonId,
        ?string $anterior,
        ?string $nuevo,
        string $accion,
        array $detalle = []
    ): void {
        CalificacionAuditoria::create([
            'calificacion_id' => $registro?->id,
            'alumno_id' => $alumnoId,
            'asignacion_materia_id' => $registro?->asignacion_materia_id ?? $canonId,
            'profesor_id' => $registro?->profesor_id,
            'licenciatura_id' => $this->licenciatura->id,
            'modalidad_id' => $this->modalidad->id,
            'generacion_id' => $this->filtrar_generacion,
            'cuatrimestre_id' => $this->filtrar_cuatrimestre,
            'user_id' => auth()->id(),
            'valor_anterior' => $anterior,
            'valor_nuevo' => $nuevo,
            'origen' => 'matriz',
            'accion' => $accion,
            'detalle' => $detalle,
        ]);
    }

    /**
     * Guardado diferencial: SOLO modifica materias cuyo valor cambió.
     * Conserva el id de calificación existente (boletas, historiales y referencias).
     */
    private function persistirCambios(array $alumnoIds, bool $permitirActualizar): array
    {
        $alumnoIds = array_values(array_unique(array_map('intval', $alumnoIds)));
        $profesores = collect($this->materias)->mapWithKeys(fn ($m) => [(int) $m->id => $m->profesor_id])->all();
        $cambios = [];

        foreach ($alumnoIds as $alumnoId) {
            foreach ($this->materias as $materia) {
                $canonId = (int) $materia->id;
                $nuevo = $this->normalizarValor($this->calificaciones[$alumnoId][$canonId] ?? null);
                $anterior = $this->normalizarValor($this->notasPersistidas[$alumnoId][$canonId] ?? null);
                if ($nuevo === $anterior) continue;
                if (!$permitirActualizar && $anterior !== null) continue;
                if ($nuevo !== null && !$this->esValorValido($nuevo)) {
                    throw new \InvalidArgumentException('Calificación no válida. Solo se permiten valores de 5 a 10 o NP.');
                }
                $cambios[$alumnoId][$canonId] = ['nuevo' => $nuevo, 'anterior' => $anterior];
            }
        }

        if (!$cambios) return ['nuevas' => 0, 'actualizadas' => 0, 'borradas' => 0];

        return DB::transaction(function () use ($alumnoIds, $profesores, $cambios) {
            $existentes = ModelsCalificacion::with('asignacionMateria:id,materia_id')
                ->whereIn('alumno_id', $alumnoIds)
                ->whereIn('asignacion_materia_id', $this->idsParaLimpiar)
                ->where('modalidad_id', $this->modalidad->id)
                ->where('licenciatura_id', $this->licenciatura->id)
                ->where('generacion_id', $this->filtrar_generacion)
                ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $porCanon = [];
            foreach ($existentes as $registro) {
                $materiaId = $registro->asignacionMateria?->materia_id;
                $canonId = $this->canonicoPorMateria[$materiaId] ?? null;
                if ($canonId !== null) {
                    $porCanon[$registro->alumno_id][$canonId][] = $registro;
                }
            }

            $resultado = ['nuevas' => 0, 'actualizadas' => 0, 'borradas' => 0];
            foreach ($cambios as $alumnoId => $materias) {
                foreach ($materias as $canonId => $cambio) {
                    $registros = $porCanon[$alumnoId][$canonId] ?? [];
                    $real = $registros
                        ? $this->normalizarValor(end($registros)->calificacion)
                        : null;
                    // Conflicto: otro usuario/docente modificó la nota después de abrir la pantalla.
                    if ($real !== $cambio['anterior']) {
                        throw new \RuntimeException('Otra persona actualizó una o más calificaciones. No se guardó ninguna edición. Descarta la edición para cargar los valores actuales.');
                    }

                    $nuevo = $cambio['nuevo'];
                    if ($nuevo === null) {
                        $idsPrevios = array_map(fn ($r) => $r->id, $registros);
                        foreach ($registros as $registro) $registro->delete();
                        if ($registros) {
                            $this->auditarCambio(null, (int) $alumnoId, (int) $canonId, $real, null,
                                'eliminada', ['registros_eliminados' => $idsPrevios]);
                            $resultado['borradas']++;
                        }
                        continue;
                    }
                    if (!$registros) {
                        $creada = ModelsCalificacion::create([
                            'alumno_id' => $alumnoId,
                            'asignacion_materia_id' => $canonId,
                            'modalidad_id' => $this->modalidad->id,
                            'generacion_id' => $this->filtrar_generacion,
                            'licenciatura_id' => $this->licenciatura->id,
                            'cuatrimestre_id' => $this->filtrar_cuatrimestre,
                            'profesor_id' => $profesores[$canonId] ?? null,
                            'calificacion' => $nuevo,
                        ]);
                        $this->auditarCambio($creada, (int) $alumnoId, (int) $canonId, null, $nuevo, 'creada');
                        $resultado['nuevas']++;
                        continue;
                    }

                    // Conserva el registro canónico, si existe, o el histórico más reciente.
                    $principal = collect($registros)->first(fn ($r) => (int) $r->asignacion_materia_id === (int) $canonId)
                        ?? end($registros);
                    $principal->calificacion = $nuevo;
                    $principal->save();
                    $eliminados = [];
                    foreach ($registros as $registro) {
                        if ((int) $registro->id !== (int) $principal->id) {
                            $eliminados[] = $registro->id;
                            $registro->delete();
                        }
                    }
                    $this->auditarCambio($principal, (int) $alumnoId, (int) $canonId, $real, $nuevo,
                        'actualizada', ['duplicados_consolidados' => $eliminados]);
                    $resultado['actualizadas']++;
                }
            }
            return $resultado;
        });
    }

    private function guardarSeleccion(array $ids, bool $permitirActualizar): void
    {
        try {
            $resultado = $this->persistirCambios($ids, $permitirActualizar);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Revisa las calificaciones', 'text' => $e->getMessage(), 'position' => 'top-end']);
            return;
        } catch (\RuntimeException $e) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Conflicto de edición', 'text' => $e->getMessage(), 'position' => 'top-end']);
            return;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'No se pudieron guardar las calificaciones', 'text' => 'No se modificaron registros. Revisa el registro de errores.', 'position' => 'top-end']);
            return;
        }

        $total = array_sum($resultado);
        if ($total === 0) {
            $this->dispatch('swal', [
                'icon' => 'info', 'title' => 'Sin calificaciones nuevas',
                'text' => $permitirActualizar ? 'No hay cambios pendientes en esta selección.' : 'Guardar añade notas nuevas. Usa Actualizar para modificar o vaciar notas existentes.',
                'position' => 'top-end',
            ]);
            return;
        }

        // La entrada del usuario se conserva (por si «Guardar» omitió modificaciones);
        // únicamente actualizamos la instantánea contra la BD.
        $this->hidratarInputsConBD();
        $this->recalcularFlags();
        $this->dispatch('swal', [
            'icon' => 'success', 'title' => 'Calificaciones sincronizadas',
            'text' => "Nuevas: {$resultado['nuevas']} · Actualizadas: {$resultado['actualizadas']} · Eliminadas: {$resultado['borradas']}.",
            'position' => 'top-end',
        ]);
    }

    public function guardarAlumno(int $alumnoId): void
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) return;
        $this->asegurarDatasetMinimo();
        $this->cargarAlumnos(); // Revalida origen y búsqueda en MySQL.
        if (!in_array($alumnoId, $this->idsAlumnosVisibles(), true)) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Alumno fuera de la selección actual', 'position' => 'top-end']);
            return;
        }
        $this->guardarSeleccion([$alumnoId], true);
    }

    public function guardarTodasLasCalificaciones(): void
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Selecciona generación y cuatrimestre', 'position' => 'top-end']);
            return;
        }
        $this->asegurarDatasetMinimo();
        $this->cargarAlumnos(); // Revalida origen, búsqueda y estado académico.
        $ids = $this->idsAlumnosVisibles();
        if (!$ids || !count($this->materias)) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'No hay alumnos o materias para guardar', 'position' => 'top-end']);
            return;
        }
        $this->guardarSeleccion($ids, $this->modoGuardar === 'actualizar');
    }

    /** Historial de la matrícula seleccionada, unificado con captura docente. */
    public function verHistorialAlumno(int $alumnoId): void
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) return;
        $this->asegurarDatasetMinimo();
        $this->cargarAlumnos();
        $alumno = collect($this->alumnos)->first(fn ($al) => (int) $al->id === $alumnoId);
        if (!$alumno || !in_array($alumnoId, $this->idsAlumnosVisibles(), true)) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Alumno fuera de la selección', 'position' => 'top-end']);
            return;
        }

        $registros = CalificacionAuditoria::query()
            ->where('alumno_id', $alumnoId)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('cuatrimestre_id', $this->filtrar_cuatrimestre)
            ->whereIn('asignacion_materia_id', $this->idsParaLimpiar)
            ->latest('id')->limit(20)->get();

        $nombresMaterias = DB::table('asignacion_materias as am')
            ->join('materias as m', 'm.id', '=', 'am.materia_id')
            ->whereIn('am.id', $registros->pluck('asignacion_materia_id')->filter()->unique()->all())
            ->pluck('m.nombre', 'am.id');
        $usuarios = DB::table('users')->whereIn('id', $registros->pluck('user_id')->filter()->unique()->all())
            ->pluck('username', 'id');
        $this->historialAlumnoNotas = $registros->map(fn ($r) => [
            'fecha' => $r->created_at?->format('d/m/Y H:i') ?? '—',
            'materia' => $nombresMaterias[$r->asignacion_materia_id] ?? 'Materia no disponible',
            'accion' => $r->accion,
            'anterior' => $r->valor_anterior,
            'nuevo' => $r->valor_nuevo,
            'usuario' => $usuarios[$r->user_id] ?? 'Sistema',
        ])->all();
        $this->nombreAlumnoHistorial = trim($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre);
        $this->alumnoHistorialId = $alumnoId;
    }

    public function cerrarHistorialAlumno(): void
    {
        $this->alumnoHistorialId = null;
        $this->nombreAlumnoHistorial = '';
        $this->historialAlumnoNotas = [];
    }

    /**
     * Informe de seguimiento: exporta SOLO estudiantes visibles por filtros,
     * sin CURP, calificaciones detalladas ni probabilidades inventadas.
     */
    public function descargarReporteAlertas()
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) return;
        $this->asegurarDatasetMinimo();
        $this->cargarAlumnos(); // Revalidar contexto, origen y búsqueda en MySQL.
        $ids = array_flip($this->idsAlumnosVisibles());
        $filas = [];
        foreach ($this->alumnos as $al) {
            if (!isset($ids[(int) $al->id])) continue;
            $riesgo = $this->alertasHistoricas[$al->id] ?? AnalizadorTrayectoriaAcademica::evaluarPeriodos([]);
            $filas[] = [
                $al->matricula ?: ($al->matricula_interna ?: ''),
                trim($al->apellido_paterno.' '.$al->apellido_materno.' '.$al->nombre),
                $al->foraneo === 'true' ? 'Foráneo' : 'Local',
                match ($riesgo['nivel']) {
                    'alto' => 'Prioridad alta',
                    'medio' => 'Preventivo',
                    'bajo' => 'Ordinario',
                    default => 'Datos insuficientes',
                },
                $riesgo['evidencia'],
                $riesgo['periodos_evaluables'],
                $riesgo['ultimo_cuatrimestre'] ?? '',
                $riesgo['tendencia'],
                $riesgo['cambio'] ?? '',
                implode('; ', $riesgo['motivos']),
                $riesgo['accion'],
            ];
        }
        if (!$filas) {
            $this->dispatch('swal', ['icon' => 'info', 'title' => 'No hay alumnos para exportar con los filtros actuales.']);
            return;
        }
        $nombre = 'seguimiento_academico_'.$this->licenciatura->id.'_'.$this->filtrar_generacion.'_'.$this->filtrar_cuatrimestre.'_'.now()->format('Ymd').'.csv';
        return response()->streamDownload(function () use ($filas) {
            $fp = fopen('php://output', 'w');
            fwrite($fp, "\xEF\xBB\xBF"); // UTF-8 BOM para Excel.
            fputcsv($fp, ['Matrícula', 'Alumno', 'Origen', 'Prioridad', 'Evidencia', 'Periodos evaluables',
                'Último cuatrimestre evaluable', 'Tendencia', 'Variación', 'Factores', 'Acción sugerida']);
            foreach ($filas as $fila) {
                // Mitiga inyección de fórmulas cuando el CSV se abra en Excel.
                fputcsv($fp, array_map(static function ($valor) {
                    $texto = (string) $valor;
                    return preg_match('/^\s*[=+\-@]/u', $texto) ? "'".$texto : $texto;
                }, $fila));
            }
            fclose($fp);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Ficha transparente de factores históricos, sin reescribir calificaciones. */
    public function verPronosticoAlumno(int $alumnoId): void
    {
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) return;
        // Revalidar contra la matrícula y el filtro de origen actuales.
        $this->asegurarDatasetMinimo();
        $alumno = Inscripcion::query()
            ->whereKey($alumnoId)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('status', 'true')
            ->when($this->filtrar_origen === 'local', fn ($q) => $q->where('foraneo', 'false'))
            ->when($this->filtrar_origen === 'foraneo', fn ($q) => $q->where('foraneo', 'true'))
            ->first();
        if (!$alumno || !collect($this->alumnos)->contains(fn ($a) => (int) $a->id === $alumnoId)) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Alumno fuera del contexto actual.']);
            return;
        }
        $evaluaciones = app(AnalizadorTrayectoriaAcademica::class)->analizar(
            [$alumnoId], (int) $this->licenciatura->id,
            (int) $this->filtrar_generacion, (int) $this->filtrar_cuatrimestre
        );
        $this->pronosticoSeleccionado = $evaluaciones[$alumnoId] ?? AnalizadorTrayectoriaAcademica::evaluarPeriodos([]);
        $this->nombreAlumnoPronostico = trim($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre);
        $this->alumnoPronosticoId = $alumnoId;
    }

    public function cerrarPronosticoAlumno(): void
    {
        $this->alumnoPronosticoId = null;
        $this->nombreAlumnoPronostico = '';
        $this->pronosticoSeleccionado = [];
    }

    /** ======================= ENVÍOS ======================= */
    public function enviarCalificacion($alumnoId, $cuatrimestreId, $generacionId, $modalidadId)
    {
        if ($this->hayCambios) {
            $this->dispatch('swal', [
                'icon' => 'warning', 'title' => 'Guarda primero las notas pendientes',
                'text' => 'El correo se genera con datos guardados, no con lo que todavía estás editando.',
                'position' => 'top-end',
            ]);
            return;
        }
        // Verificar de nuevo el contexto y el origen del alumno antes del envío individual.
        $autorizado = (int) $generacionId === (int) $this->filtrar_generacion
            && (int) $cuatrimestreId === (int) $this->filtrar_cuatrimestre
            && (int) $modalidadId === (int) $this->modalidad->id
            && Inscripcion::query()->whereKey((int) $alumnoId)
                ->where('licenciatura_id', $this->licenciatura->id)
                ->where('modalidad_id', $this->modalidad->id)
                ->where('generacion_id', $this->filtrar_generacion)
                ->where('status', 'true')
                ->when($this->filtrar_origen === 'local', fn ($q) => $q->where('foraneo', 'false'))
                ->when($this->filtrar_origen === 'foraneo', fn ($q) => $q->where('foraneo', 'true'))
                ->exists();
        if (!$autorizado) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'El alumno no pertenece al filtro actual.']);
            return;
        }

        $boletas = app(BoletaService::class);

        try {
            $dataset = $boletas->datasetBoleta(
                (int) $alumnoId,
                (int) $this->licenciatura->id,
                (int) $modalidadId,
                (int) $generacionId,
                (int) $cuatrimestreId,
                true
            );
        } catch (\Throwable $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No fue posible preparar la boleta',
                'text' => 'Verifica que el alumno siga activo y pertenezca al contexto seleccionado.',
                'position' => 'top-end',
            ]);
            return;
        }

        if ($dataset['calificaciones']->isEmpty()) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'El alumno no tiene calificaciones para este cuatrimestre.',
                'position' => 'top-end',
            ]);
            return;
        }

        $correo = $dataset['inscripcion']->user->email ?? null;
        if (!$correo) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'El alumno no tiene correo registrado',
                'position' => 'top-end',
            ]);
            return;
        }

        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Enviando correo...',
            'position' => 'top',
        ]);

        Mail::to($correo)->queue(new CalificacionMail(
            $dataset['calificaciones'],
            $dataset['escuela'],
            $dataset['inscripcion'],
            $dataset['licenciatura'],
            $dataset['generacion'],
            $dataset['cuatrimestre'],
            $dataset['ciclo_escolar'],
            $dataset['periodo']
        ));

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Correo encolado correctamente.',
            'position' => 'top-end',
        ]);
    }

    public function enviarCalificacionesMasivas()
    {
        if ($this->hayCambios) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Primero guarda tus cambios',
                'text' => 'El envío por correo utiliza las calificaciones guardadas, no las pendientes de captura.',
                'position' => 'top-end',
            ]);
            return;
        }
        if (!$this->filtrar_generacion || !$this->filtrar_cuatrimestre) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Debes seleccionar generación y cuatrimestre.',
                'position' => 'top-end',
            ]);
            return;
        }

        // Solo alumnos activos del origen solicitado. Evita afectar a otros alumnos.
        $alumnos = Inscripcion::with('user')
            ->where('generacion_id', $this->filtrar_generacion)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('status', 'true')
            ->when($this->filtrar_origen === 'local', fn ($q) => $q->where('foraneo', 'false'))
            ->when($this->filtrar_origen === 'foraneo', fn ($q) => $q->where('foraneo', 'true'))
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        $boletas = app(BoletaService::class);
        $encolados = 0;
        $sinCorreo = 0;
        $sinCalificaciones = 0;

        foreach ($alumnos as $inscripcion) {
            $correo = $inscripcion->user->email ?? null;
            if (!$correo) {
                $sinCorreo++;
                continue;
            }

            try {
                $dataset = $boletas->datasetBoleta(
                    (int) $inscripcion->id,
                    (int) $this->licenciatura->id,
                    (int) $this->modalidad->id,
                    (int) $this->filtrar_generacion,
                    (int) $this->filtrar_cuatrimestre,
                    true
                );
            } catch (\Throwable $e) {
                continue;
            }

            if ($dataset['calificaciones']->isEmpty()) {
                $sinCalificaciones++;
                continue;
            }

            Mail::to($correo)->queue(new CalificacionMail(
                $dataset['calificaciones'],
                $dataset['escuela'],
                $dataset['inscripcion'],
                $dataset['licenciatura'],
                $dataset['generacion'],
                $dataset['cuatrimestre'],
                $dataset['ciclo_escolar'],
                $dataset['periodo']
            ));

            $encolados++;
        }

        $this->dispatch('swal', [
            'icon' => $encolados > 0 ? 'success' : 'warning',
            'title' => $encolados > 0
                ? "{$encolados} correo(s) encolado(s) correctamente."
                : 'No se encolaron correos.',
            'text' => "Sin correo: {$sinCorreo}. Sin calificaciones: {$sinCalificaciones}.",
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

        $conteos = ['total' => 0, 'todos' => 0, 'local' => 0, 'foraneo' => 0];
        if ($this->filtrar_generacion && $this->filtrar_cuatrimestre) {
            $registro = Inscripcion::query()
                ->where('licenciatura_id', $this->licenciatura->id)
                ->where('modalidad_id', $this->modalidad->id)
                ->where('generacion_id', $this->filtrar_generacion)
                ->where('status', 'true')
                ->selectRaw("COUNT(*) as total, SUM(CASE WHEN foraneo = 'true' THEN 1 ELSE 0 END) as foraneos")
                ->first();
            $conteos = [
                'total' => (int) ($registro?->total ?? 0),
                'todos' => (int) ($registro?->total ?? 0),
                'foraneo' => (int) ($registro?->foraneos ?? 0),
                'local' => (int) ($registro?->total ?? 0) - (int) ($registro?->foraneos ?? 0),
            ];
        }

        $diagnosticos = [];
        $indicadores = [
            'alumnos' => count($this->alumnos), 'celdas' => 0,
            'capturadas' => 0, 'pendientes' => 0, 'riesgo' => 0,
            'completos' => 0, 'np' => 0, 'avance' => 0,
        ];
        foreach ($this->alumnos as $al) {
            $diagnostico = $this->diagnosticoAlumno((int) $al->id, $this->calificaciones);
            $diagnosticos[$al->id] = $diagnostico;
            $indicadores['celdas'] += $diagnostico['total'];
            $indicadores['capturadas'] += $diagnostico['capturadas'];
            $indicadores['pendientes'] += $diagnostico['pendientes'];
            $indicadores['np'] += $diagnostico['np'];
            if ($diagnostico['riesgo']) $indicadores['riesgo']++;
            if ($diagnostico['completo']) $indicadores['completos']++;
        }
        $indicadores['avance'] = $indicadores['celdas']
            ? (int) round($indicadores['capturadas'] * 100 / $indicadores['celdas']) : 0;

        $conteosPredictivos = ['alto' => 0, 'medio' => 0, 'bajo' => 0, 'sin_datos' => 0];
        foreach ($this->alumnos as $al) {
            $nivel = $this->alertasHistoricas[$al->id]['nivel'] ?? 'sin_datos';
            $conteosPredictivos[$nivel]++;
        }
        // Máximo cinco casos de atención prioritaria; botones abren una ficha detallada.
        $atencionPrioritaria = collect($this->alumnos)
            ->filter(fn ($al) => in_array($this->alertasHistoricas[$al->id]['nivel'] ?? '', ['alto', 'medio'], true))
            ->sortBy(fn ($al) => ($this->alertasHistoricas[$al->id]['nivel'] ?? '') === 'alto' ? 0 : 1)
            ->take(5)->values();

        $visibles = $this->idsAlumnosVisibles();
        $alumnosVista = collect($this->alumnos)->filter(fn ($al) => in_array((int) $al->id, $visibles, true))->values();

        return view('livewire.admin.licenciaturas.submodulo.calificacion', [
            'conteosOrigen' => $conteos,
            'conteosPredictivos' => $conteosPredictivos,
            'atencionPrioritaria' => $atencionPrioritaria,
            'alertasHistoricas' => $this->alertasHistoricas,
            'generaciones' => $this->generaciones,
            'cuatrimestres' => $this->cuatrimestres,
            'alumnos' => collect($this->alumnos),
            'alumnosVista' => $alumnosVista,
            'diagnosticos' => $diagnosticos,
            'indicadores' => $indicadores,
            'materias' => collect($this->materias),
            'todas_calificaciones_guardadas' => $this->todas_calificaciones_guardadas,
            'hayCambios' => $this->hayCambios,
            'periodo' => $this->periodo,
            'generacion_filtrada' => $this->generacion_filtrada,
            'yaExistenEnBD' => $this->yaExistenEnBD,
        ]);
    }
}
