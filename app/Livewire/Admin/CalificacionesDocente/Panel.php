<?php

namespace App\Livewire\Admin\CalificacionesDocente;

use App\Exports\CalificacionesDocente\PlantillaDocenteExport;
use App\Exports\CalificacionesDocente\PlantillaMateriaExport;
use App\Models\Calificacion;
use App\Models\CalificacionAuditoria;
use App\Models\CalificacionDocenteCaptura;
use App\Models\Inscripcion;
use App\Services\CalificacionesDocenteService;
use App\Services\CalificacionesHistoricasService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Panel extends Component
{
    use WithFileUploads;

    public ?int $profesorId = null;
    public ?int $licenciaturaId = null;
    public ?int $modalidadId = null;
    public ?int $generacionId = null;
    public ?int $cuatrimestreId = null;
    public string $estadoFiltro = '';

    public string $modo = 'materia';
    public string $buscarAlumnoGlobal = '';
    public ?int $alumnoSeleccionadoId = null;
    public string $pendientesFiltro = 'todos';
    public string $buscarAlumno = '';
    public string $origenFiltro = 'todos';
    public array $contextoSeleccionado = [];
    public array $asignacionesMatriz = [];
    public array $alumnos = [];
    public array $calificaciones = [];
    public array $calificacionesGuardadas = [];

    // Historial administrativo independiente del cuatrimestre actual.
    public string $buscarAlumnoHistorico = '';
    public ?int $alumnoHistoricoId = null;
    public array $historialRegistros = [];
    public array $notasHistoricas = [];
    public array $notasHistoricasGuardadas = [];
    public string $motivoCorreccionHistorica = '';

    public $archivoExcel = null;
    public array $vistaPreviaImportacion = [];
    public array $resumenImportacion = [];

    public ?string $fechaLimite = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('calificaciones-docente.ver'), 403);
        abort_unless($this->puedeAdministrar() || $this->esProfesorRestringido(), 403);

        if ($this->esProfesorRestringido()) {
            $profesorId = $this->service()->profesorId(auth()->user());
            abort_unless($profesorId, 403, 'El usuario Profesor no tiene un perfil de profesor asociado.');
            $this->profesorId = $profesorId;
        }
    }

    public function updatedProfesorId(): void { $this->cerrarCaptura(); }
    public function updatedLicenciaturaId(): void { $this->cerrarCaptura(); }
    public function updatedModalidadId(): void { $this->cerrarCaptura(); }
    public function updatedGeneracionId(): void { $this->cerrarCaptura(); }
    public function updatedCuatrimestreId(): void { $this->cerrarCaptura(); }
    public function updatedOrigenFiltro(): void { $this->pendientesFiltro = 'todos'; }

    public function limpiarFiltros(): void
    {
        if (!$this->esProfesorRestringido()) {
            $this->profesorId = null;
        }
        $this->licenciaturaId = null;
        $this->modalidadId = null;
        $this->generacionId = null;
        $this->cuatrimestreId = null;
        $this->estadoFiltro = '';
        $this->origenFiltro = 'todos';
        $this->buscarAlumnoGlobal = '';
        $this->cerrarCaptura();
    }

    public function cargarMateria(int $asignacionId, int $generacionId): void
    {
        $contexto = $this->service()->contextoAsignacion(auth()->user(), $asignacionId, $generacionId);
        abort_unless($contexto, 403);

        $estado = $this->estadoReal($contexto);
        $contextoArray = array_merge((array) $contexto, [
            'estado' => $estado['estado'],
            'bloqueada_docente' => $estado['bloqueada_docente'],
            'fecha_limite' => $estado['fecha_limite'],
        ]);

        $this->modo = 'materia';
        $this->alumnoSeleccionadoId = null;
        $this->contextoSeleccionado = $contextoArray;
        $this->asignacionesMatriz = [$contextoArray];
        $this->buscarAlumno = '';
        $this->cargarAlumnosYCalificaciones(collect([$contexto]));
        $this->cargarFechaLimite();
        $this->limpiarImportacion();
    }

    public function abrirMatriz(): void
    {
        $required = [$this->profesorId, $this->licenciaturaId, $this->modalidadId, $this->generacionId, $this->cuatrimestreId];
        if (collect($required)->contains(fn ($v) => empty($v))) {
            $this->swal('warning', 'Selecciona docente, licenciatura, modalidad, generación y cuatrimestre para abrir la matriz.');
            return;
        }

        $contextos = $this->service()->asignaciones(auth()->user(), $this->filtros())
            ->filter(fn ($r) => (int) $r->profesor_id === (int) $this->profesorId)
            ->values();

        if ($contextos->isEmpty()) {
            $this->swal('warning', 'No hay materias calificables en horario con esos filtros.');
            return;
        }

        $this->modo = 'matriz';
        $this->alumnoSeleccionadoId = null;
        $this->contextoSeleccionado = (array) $contextos->first();
        $this->asignacionesMatriz = $contextos->map(fn ($r) => (array) $r)->all();
        $this->buscarAlumno = '';
        $this->cargarAlumnosYCalificaciones($contextos);
        $this->fechaLimite = null;
        $this->limpiarImportacion();
    }

    /**
     * Captura transversal por estudiante. Los profesores sólo ven las materias
     * que les corresponden y cada contexto se vuelve a validar al guardar.
     */
    public function abrirAlumno(int $alumnoId, int $asignacionId, int $generacionId): void
    {
        $ancla = $this->service()->contextoAsignacion(auth()->user(), $asignacionId, $generacionId);
        abort_unless($ancla, 403);

        $alumno = $this->service()->alumnosParaContexto($ancla)->firstWhere('id', $alumnoId);
        abort_unless($alumno, 403, 'El alumno no pertenece al grupo activo.');

        $filtros = [
            'licenciatura_id' => (int) $ancla->licenciatura_id,
            'modalidad_id' => (int) $ancla->modalidad_id,
            'generacion_id' => (int) $ancla->generacion_id,
            'cuatrimestre_id' => (int) $ancla->cuatrimestre_id,
        ];
        $contextos = $this->service()->asignaciones(auth()->user(), $filtros)->values();
        abort_if($contextos->isEmpty(), 403);

        $this->modo = 'alumno';
        $this->alumnoSeleccionadoId = $alumnoId;
        $this->contextoSeleccionado = (array) $ancla;
        $this->asignacionesMatriz = $contextos->map(fn ($r) => (array) $r)->all();
        $this->origenFiltro = 'todos';
        $this->pendientesFiltro = 'todos';
        $this->buscarAlumno = '';
        $this->cargarAlumnosYCalificaciones($contextos, $alumnoId);
        $this->fechaLimite = null;
        $this->limpiarImportacion();
        $this->dispatch('captura-docente-abierta');
    }

    /** Abre todas las notas registradas de un alumno, incluso bajas y periodos antiguos. */
    public function abrirHistorialAlumno(int $alumnoId): void
    {
        abort_unless($this->puedeAdministrar() && auth()->user()?->can('calificaciones-docente.ver'), 403);
        $this->historico()->alumno(auth()->user(), $alumnoId);

        $this->cerrarCaptura();
        $this->modo = 'historial';
        $this->alumnoHistoricoId = $alumnoId;
        $this->buscarAlumnoHistorico = '';
        $this->motivoCorreccionHistorica = '';
        $this->recargarNotasHistoricas();
        $this->dispatch('captura-docente-abierta');
    }

    /** Corrección exclusiva de Control Escolar y del registro original. */
    public function guardarCalificacionHistorica(int $notaId): void
    {
        abort_unless($this->puedeAdministrar() && auth()->user()?->can('calificaciones-docente.ver'), 403);
        abort_unless($this->modo === 'historial' && $this->alumnoHistoricoId, 403);

        $nuevo = $this->normalizarCalificacion($this->notasHistoricas[$notaId] ?? null);
        if (!$this->calificacionValida($nuevo)) {
            $this->addError('notasHistoricas.'.$notaId, 'Escribe una nota entre 5 y 10, o NP.');
            return;
        }
        $this->resetValidation('notasHistoricas.'.$notaId);
        $this->validate([
            'motivoCorreccionHistorica' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'motivoCorreccionHistorica.required' => 'Indica por qué se corrige la nota.',
            'motivoCorreccionHistorica.min' => 'El motivo debe tener por lo menos 10 caracteres.',
        ]);

        // Conservar borradores de otras materias: el guardado sólo afecta una nota.
        $borradores = collect($this->notasHistoricas)->filter(fn ($valor, $id) =>
            (int) $id !== $notaId &&
            (string) $valor !== (string) ($this->notasHistoricasGuardadas[$id] ?? '')
        )->all();
        $snapshots = $this->notasHistoricasGuardadas;

        $cambio = $this->historico()->corregirNota(
            auth()->user(),
            $this->alumnoHistoricoId,
            $notaId,
            $nuevo,
            $this->motivoCorreccionHistorica,
            $this->notasHistoricasGuardadas[$notaId] ?? null
        );
        $this->recargarNotasHistoricas();
        foreach ($borradores as $id => $valor) {
            if (array_key_exists($id, $this->notasHistoricas)) {
                $this->notasHistoricas[$id] = $valor;
                $this->notasHistoricasGuardadas[$id] = $snapshots[$id] ?? null;
            }
        }
        $this->motivoCorreccionHistorica = '';
        $this->swal('success', $cambio
            ? 'Calificación histórica corregida y auditada.'
            : 'La nota ya tenía el mismo valor.');
    }

    public function cerrarHistorialAlumno(): void
    {
        $this->cerrarCaptura();
    }

    private function recargarNotasHistoricas(): void
    {
        abort_unless($this->alumnoHistoricoId && $this->puedeAdministrar(), 403);
        $this->historialRegistros = $this->historico()
            ->notas(auth()->user(), $this->alumnoHistoricoId)
            ->map(fn ($registro) => (array) $registro)->all();
        $this->notasHistoricas = collect($this->historialRegistros)
            ->mapWithKeys(fn ($nota) => [(int) $nota['id'] => $nota['calificacion']])->all();
        $this->notasHistoricasGuardadas = $this->notasHistoricas;
    }

    /** Guarda exclusivamente una casilla, sin descartar otras ediciones del formulario. */
    public function guardarCalificacionIndividual(int $alumnoId, int $asignacionId): void
    {
        $contexto = $this->contextoEditableYAlumno($alumnoId, $asignacionId);
        if (!$contexto) return;

        $nuevo = $this->normalizarCalificacion($this->calificaciones[$alumnoId][$asignacionId] ?? null);
        if ($nuevo === null || !$this->calificacionValida($nuevo)) {
            $this->swal('warning', 'Escribe una calificación válida de 5 a 10 o NP. El campo vacío no modifica la nota.');
            return;
        }

        $cambio = DB::transaction(function () use ($alumnoId, $asignacionId, $contexto, $nuevo) {
            $cambio = $this->guardarUnaCalificacion($alumnoId, $contexto, $nuevo, 'individual');
            $this->actualizarEstadoCalculado($contexto);
            return $cambio;
        });
        $this->calificaciones[$alumnoId][$asignacionId] = $nuevo;
        $this->calificacionesGuardadas[$alumnoId][$asignacionId] = $nuevo;
        $this->swal('success', $cambio ? 'Calificación individual guardada.' : 'La calificación ya estaba actualizada.');
    }

    /** Guarda sólo las materias de la fila seleccionada. */
    public function guardarAlumnoFila(int $alumnoId): void
    {
        abort_unless($this->alumnosVisiblesCaptura()->contains('id', $alumnoId), 403);
        $contextos = $this->contextosAutorizadosActuales()->keyBy('asignacion_materia_id');
        $valores = (array) ($this->calificaciones[$alumnoId] ?? []);
        $guardar = [];

        foreach ($valores as $asignacionId => $valor) {
            $contexto = $contextos->get((int) $asignacionId);
            if (!$contexto || $this->bloqueadaParaDocente($contexto)) continue;
            $nuevo = $this->normalizarCalificacion($valor);
            if ($nuevo === null) continue;
            if (!$this->calificacionValida($nuevo)) {
                $this->swal('error', 'La fila tiene valores inválidos. Usa de 5 a 10 o NP.');
                return;
            }
            $guardar[] = [$contexto, $nuevo];
        }

        if (!$guardar) {
            $this->swal('warning', 'Esta fila no contiene calificaciones nuevas editables.');
            return;
        }

        $cambios = DB::transaction(function () use ($alumnoId, $guardar) {
            $cambios = 0;
            foreach ($guardar as [$contexto, $nuevo]) {
                $cambios += (int) $this->guardarUnaCalificacion($alumnoId, $contexto, $nuevo, 'individual');
                $this->actualizarEstadoCalculado($contexto);
                $this->calificaciones[$alumnoId][$contexto->asignacion_materia_id] = $nuevo;
                $this->calificacionesGuardadas[$alumnoId][$contexto->asignacion_materia_id] = $nuevo;
            }
            return $cambios;
        });

        $this->swal('success', $cambios ? "Se guardaron {$cambios} cambio(s) del alumno." : 'La fila ya estaba actualizada.');
    }

    private function contextoEditableYAlumno(int $alumnoId, int $asignacionId): ?object
    {
        // La propiedad pública no es una autorización: releemos asignación y pertenencia.
        $contexto = $this->contextosAutorizadosActuales()->firstWhere('asignacion_materia_id', $asignacionId);
        abort_unless($contexto && $this->alumnosVisiblesCaptura()->contains('id', $alumnoId), 403);
        if ($this->bloqueadaParaDocente($contexto)) {
            $this->swal('warning', 'La materia está entregada, validada o fuera del plazo de captura.');
            return null;
        }
        return $contexto;
    }

    public function cerrarCaptura(): void
    {
        $this->modo = 'materia';
        $this->alumnoSeleccionadoId = null;
        $this->alumnoHistoricoId = null;
        $this->historialRegistros = [];
        $this->notasHistoricas = [];
        $this->notasHistoricasGuardadas = [];
        $this->motivoCorreccionHistorica = '';
        $this->pendientesFiltro = 'todos';
        $this->contextoSeleccionado = [];
        $this->asignacionesMatriz = [];
        $this->alumnos = [];
        $this->calificaciones = [];
        $this->calificacionesGuardadas = [];
        $this->buscarAlumno = '';
        $this->fechaLimite = null;
        $this->limpiarImportacion();
    }

    public function guardarCalificaciones(): void
    {
        if (empty($this->contextoSeleccionado) || empty($this->asignacionesMatriz)) {
            $this->swal('warning', 'Primero abre una materia o la captura masiva.');
            return;
        }

        $contextos = $this->contextosAutorizadosActuales();
        if ($contextos->isEmpty()) {
            abort(403);
        }

        $contextosEditables = $contextos
            ->reject(fn ($contexto) => $this->bloqueadaParaDocente($contexto))
            ->values();

        if ($contextosEditables->isEmpty()) {
            $this->swal('warning', 'La captura está cerrada, entregada o fuera de fecha límite.');
            return;
        }

        $alumnosPermitidos = $this->alumnosVisiblesCaptura()->pluck('id')->map(fn ($id) => (int) $id)->flip();
        $contextosPorId = $contextosEditables->keyBy('asignacion_materia_id');
        $errores = [];
        $cambios = 0;

        foreach ($this->calificaciones as $alumnoId => $porMateria) {
            if (!$alumnosPermitidos->has((int) $alumnoId)) {
                continue;
            }

            foreach ((array) $porMateria as $asignacionId => $valor) {
                if (!$contextosPorId->has((int) $asignacionId)) {
                    continue;
                }

                $normalizado = $this->normalizarCalificacion($valor);
                if ($normalizado === null) {
                    continue; // Vacío = no modificar.
                }

                if (!$this->calificacionValida($normalizado)) {
                    $errores[] = "Alumno {$alumnoId}, materia {$asignacionId}: valor no válido.";
                }
            }
        }

        if ($errores) {
            $this->swal('error', 'Hay calificaciones inválidas. Usa valores de 5 a 10 o NP.');
            return;
        }

        DB::transaction(function () use ($contextosPorId, $alumnosPermitidos, &$cambios) {
            foreach ($this->calificaciones as $alumnoId => $porMateria) {
                if (!$alumnosPermitidos->has((int) $alumnoId)) {
                    continue;
                }

                foreach ((array) $porMateria as $asignacionId => $valor) {
                    $contexto = $contextosPorId->get((int) $asignacionId);
                    if (!$contexto) {
                        continue;
                    }

                    $normalizado = $this->normalizarCalificacion($valor);
                    if ($normalizado === null) {
                        continue;
                    }

                    if ($this->guardarUnaCalificacion((int) $alumnoId, $contexto, $normalizado, 'manual')) {
                        $cambios++;
                    }
                }
            }

            foreach ($contextosPorId as $contexto) {
                $this->actualizarEstadoCalculado($contexto);
            }
        });

        $this->recargarCapturaActual();
        $this->swal('success', $cambios > 0 ? "Se guardaron {$cambios} cambio(s)." : 'No había cambios por guardar.');
    }

    public function eliminarCalificacion(int $alumnoId, int $asignacionId): void
    {
        $contexto = $this->contextosAutorizadosActuales()->firstWhere('asignacion_materia_id', $asignacionId);
        abort_unless($contexto, 403);

        if ($this->bloqueadaParaDocente($contexto)) {
            $this->swal('warning', 'La captura está bloqueada.');
            return;
        }

        abort_unless($this->alumnosVisiblesCaptura()->contains('id', $alumnoId), 403);
        $alumno = $this->service()->alumnosParaContexto($contexto)->firstWhere('id', $alumnoId);
        abort_unless($alumno, 403);

        DB::transaction(function () use ($alumnoId, $asignacionId, $contexto) {
            $calificacion = $this->queryCalificacion($alumnoId, $contexto)->first();
            if (!$calificacion) {
                return;
            }

            $anterior = $calificacion->calificacion;
            $id = $calificacion->id;
            $calificacion->delete();

            CalificacionAuditoria::create([
                'calificacion_id' => null,
                'alumno_id' => $alumnoId,
                'asignacion_materia_id' => $asignacionId,
                'profesor_id' => $contexto->profesor_id,
                'licenciatura_id' => (int) $contexto->licenciatura_id,
                'modalidad_id' => (int) $contexto->modalidad_id,
                'generacion_id' => (int) $contexto->generacion_id,
                'cuatrimestre_id' => (int) $contexto->cuatrimestre_id,
                'user_id' => auth()->id(),
                'valor_anterior' => $anterior,
                'valor_nuevo' => null,
                'origen' => 'manual',
                'accion' => 'eliminada',
                'detalle' => ['calificacion_eliminada_id' => $id],
            ]);

            $this->actualizarEstadoCalculado($contexto);
        });

        $this->recargarCapturaActual();
        $this->swal('success', 'Calificación eliminada de forma explícita.');
    }

    public function descargarPlantillaMateria()
    {
        if (empty($this->contextoSeleccionado)) {
            $this->swal('warning', 'Abre una materia antes de descargar la plantilla.');
            return null;
        }

        $contexto = $this->service()->contextoAsignacion(
            auth()->user(),
            (int) $this->contextoSeleccionado['asignacion_materia_id'],
            (int) $this->contextoSeleccionado['generacion_id']
        );
        abort_unless($contexto, 403);

        $alumnos = $this->service()->alumnosParaContexto($contexto);
        $calificaciones = $this->calificacionesActuales($contexto, $alumnos->pluck('id')->all());
        $nombre = $this->nombreArchivo('PLANTILLA', $contexto) . '.xlsx';

        return Excel::download(new PlantillaMateriaExport($contexto, $alumnos, $calificaciones), $nombre);
    }

    public function descargarPlantillaDocente()
    {
        if (!$this->profesorId) {
            $this->swal('warning', 'Selecciona un docente para generar su plantilla completa.');
            return null;
        }

        $contextos = $this->service()->asignaciones(auth()->user(), $this->filtros())
            ->filter(fn ($r) => (int) $r->profesor_id === (int) $this->profesorId)
            ->values();

        if ($contextos->isEmpty()) {
            $this->swal('warning', 'El docente no tiene materias calificables en horario con esos filtros.');
            return null;
        }

        $resolver = function (object $contexto): array {
            $alumnos = $this->service()->alumnosParaContexto($contexto);
            return [
                'alumnos' => $alumnos,
                'calificaciones' => $this->calificacionesActuales($contexto, $alumnos->pluck('id')->all()),
            ];
        };

        $nombre = 'PLANTILLA_COMPLETA_DOCENTE_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new PlantillaDocenteExport($contextos, $resolver), $nombre);
    }

    public function previsualizarImportacion(): void
    {
        $this->validate([
            'archivoExcel' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ], [
            'archivoExcel.required' => 'Selecciona una plantilla Excel.',
            'archivoExcel.mimes' => 'El archivo debe ser XLSX o XLS.',
            'archivoExcel.max' => 'El archivo no debe superar 10 MB.',
        ]);

        $this->vistaPreviaImportacion = [];
        $this->resumenImportacion = [];

        try {
            $spreadsheet = IOFactory::load($this->archivoExcel->getRealPath());
            $filas = [];

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $headers = [];
                $ultimaColumna = min(20, Coordinate::columnIndexFromString($sheet->getHighestColumn()));
                foreach (range(1, max(1, $ultimaColumna)) as $column) {
                    $headers[$column] = $this->normalizarEncabezado((string) $sheet->getCell(Coordinate::stringFromColumnIndex($column).'1')->getValue());
                }

                $idx = array_flip($headers);
                $identificadorHeader = isset($idx['id_de_control_interno'])
                    ? 'id_de_control_interno'
                    : (isset($idx['matricula']) ? 'matricula' : null); // compatibilidad con plantillas anteriores
                $required = ['calificacion', 'alumno_id', 'asignacion_materia_id', 'generacion_id'];
                if (!$identificadorHeader || collect($required)->contains(fn ($h) => !isset($idx[$h]))) {
                    continue; // p.ej. hoja INSTRUCCIONES
                }

                for ($row = 2; $row <= $sheet->getHighestDataRow(); $row++) {
                    $valor = $sheet->getCell(Coordinate::stringFromColumnIndex($idx['calificacion']).$row)->getValue();
                    if ($valor === null || trim((string) $valor) === '') {
                        continue; // vacío = no modificar
                    }

                    $filas[] = $this->analizarFilaImportacion(
                        $sheet->getTitle(),
                        $row,
                        (int) $sheet->getCell(Coordinate::stringFromColumnIndex($idx['alumno_id']).$row)->getValue(),
                        (int) $sheet->getCell(Coordinate::stringFromColumnIndex($idx['asignacion_materia_id']).$row)->getValue(),
                        (int) $sheet->getCell(Coordinate::stringFromColumnIndex($idx['generacion_id']).$row)->getValue(),
                        (string) $sheet->getCell(Coordinate::stringFromColumnIndex($idx[$identificadorHeader]).$row)->getFormattedValue(),
                        $valor
                    );

                    if (count($filas) > 5000) {
                        throw new \RuntimeException('La plantilla supera el límite de 5,000 filas con calificación.');
                    }
                }
            }

            if (!$filas) {
                $this->swal('warning', 'No se encontraron filas con calificaciones en una plantilla válida.');
                return;
            }

            $duplicadas = collect($filas)
                ->groupBy(fn ($f) => implode(':', [$f['alumno_id'], $f['asignacion_materia_id'], $f['generacion_id']]))
                ->filter(fn ($grupo) => $grupo->count() > 1)
                ->keys()
                ->flip();

            if ($duplicadas->isNotEmpty()) {
                foreach ($filas as &$fila) {
                    $clave = implode(':', [$fila['alumno_id'], $fila['asignacion_materia_id'], $fila['generacion_id']]);
                    if ($duplicadas->has($clave)) {
                        $fila['accion'] = 'error';
                        $fila['mensaje'] = 'La misma combinación alumno/materia aparece más de una vez en el archivo.';
                    }
                }
                unset($fila);
            }

            $this->vistaPreviaImportacion = $filas;
            $this->resumenImportacion = [
                'total' => count($filas),
                'crear' => collect($filas)->where('accion', 'crear')->count(),
                'actualizar' => collect($filas)->where('accion', 'actualizar')->count(),
                'sin_cambio' => collect($filas)->where('accion', 'sin_cambio')->count(),
                'errores' => collect($filas)->where('accion', 'error')->count(),
            ];
        } catch (\Throwable $e) {
            report($e);
            $this->limpiarImportacion(false);
            $this->swal('error', 'No fue posible leer la plantilla: ' . $e->getMessage());
        }
    }

    public function confirmarImportacion(): void
    {
        if (empty($this->vistaPreviaImportacion)) {
            $this->swal('warning', 'Primero genera la vista previa.');
            return;
        }

        if (($this->resumenImportacion['errores'] ?? 0) > 0) {
            $this->swal('warning', 'Corrige los errores de la plantilla antes de confirmar.');
            return;
        }

        $aplicadas = 0;

        try {
            DB::transaction(function () use (&$aplicadas) {
                foreach ($this->vistaPreviaImportacion as $fila) {
                    if (!in_array($fila['accion'], ['crear', 'actualizar'], true)) {
                        continue;
                    }

                    $contexto = $this->service()->contextoAsignacion(
                        auth()->user(),
                        (int) $fila['asignacion_materia_id'],
                        (int) $fila['generacion_id']
                    );
                    abort_unless($contexto, 403);

                    if ($this->bloqueadaParaDocente($contexto)) {
                        throw new \RuntimeException("La materia {$contexto->materia} está bloqueada para edición.");
                    }

                    if ($this->guardarUnaCalificacion(
                        (int) $fila['alumno_id'],
                        $contexto,
                        (string) $fila['nuevo'],
                        'excel'
                    )) {
                        $aplicadas++;
                    }
                }

                $contextos = collect($this->vistaPreviaImportacion)
                    ->map(fn ($f) => [$f['asignacion_materia_id'], $f['generacion_id']])
                    ->unique(fn ($x) => $x[0] . ':' . $x[1]);

                foreach ($contextos as [$asignacionId, $generacionId]) {
                    $contexto = $this->service()->contextoAsignacion(auth()->user(), (int) $asignacionId, (int) $generacionId);
                    if ($contexto) {
                        $this->actualizarEstadoCalculado($contexto);
                    }
                }
            });
        } catch (\Throwable $e) {
            report($e);
            $this->swal('error', 'No se aplicó ningún cambio: ' . $e->getMessage());
            return;
        }

        $this->limpiarImportacion();
        $this->recargarCapturaActual();
        $this->swal('success', "Importación confirmada: {$aplicadas} cambio(s) aplicado(s).");
    }

    public function cancelarImportacion(): void
    {
        $this->limpiarImportacion();
    }

    public function guardarFechaLimite(): void
    {
        abort_unless($this->puedeAdministrar(), 403);
        if (empty($this->contextoSeleccionado) || $this->modo !== 'materia') {
            $this->swal('warning', 'Abre una materia para definir su fecha límite.');
            return;
        }

        $this->validate(['fechaLimite' => ['nullable', 'date']]);
        $contexto = $this->contextosAutorizadosActuales()->first();
        abort_unless($contexto, 403);

        $registro = $this->capturaRegistro($contexto);
        $registro->fecha_limite = $this->fechaLimite ?: null;
        $registro->save();

        $this->swal('success', $this->fechaLimite ? 'Fecha límite actualizada.' : 'Fecha límite eliminada.');
    }

    public function entregar(): void
    {
        $contexto = $this->contextosAutorizadosActuales()->first();
        if (!$contexto || $this->modo !== 'materia') {
            $this->swal('warning', 'La entrega se realiza una materia a la vez.');
            return;
        }

        if ($this->bloqueadaParaDocente($contexto)) {
            $this->swal('warning', 'La captura está cerrada o fuera de la fecha límite.');
            return;
        }

        $estado = $this->estadoReal($contexto);
        if ($estado['estado'] !== 'completa') {
            $this->swal('warning', "Faltan {$estado['pendientes']} calificación(es) antes de entregar.");
            return;
        }

        $registro = $this->capturaRegistro($contexto);
        $registro->update([
            'estado' => 'entregada',
            'entregado_at' => now(),
            'entregado_por' => auth()->id(),
        ]);

        $this->recargarCapturaActual();
        $this->swal('success', 'Calificaciones entregadas. La captura quedó bloqueada para el docente.');
    }

    public function validarEntrega(): void
    {
        abort_unless($this->puedeAdministrar(), 403);
        $contexto = $this->contextosAutorizadosActuales()->first();
        abort_unless($contexto, 403);

        $registro = $this->capturaRegistro($contexto);
        if ($registro->estado !== 'entregada') {
            $this->swal('warning', 'La materia debe estar entregada antes de validarla.');
            return;
        }

        $registro->update([
            'estado' => 'validada',
            'validado_at' => now(),
            'validado_por' => auth()->id(),
        ]);

        $this->recargarCapturaActual();
        $this->swal('success', 'Entrega validada y cerrada.');
    }

    public function reabrirCaptura(): void
    {
        abort_unless($this->puedeAdministrar(), 403);
        $contexto = $this->contextosAutorizadosActuales()->first();
        abort_unless($contexto, 403);

        $registro = $this->capturaRegistro($contexto);
        $registro->update([
            'estado' => 'parcial',
            'entregado_at' => null,
            'validado_at' => null,
            'reabierto_at' => now(),
            'reabierto_por' => auth()->id(),
        ]);
        $this->actualizarEstadoCalculado($contexto);

        $this->recargarCapturaActual();
        $this->swal('success', 'Captura reabierta. El docente puede volver a editarla mientras esté dentro de la fecha límite.');
    }

    public function render()
    {
        $opciones = $this->service()->opciones(auth()->user(), $this->filtros());
        $asignacionesBase = $this->service()->asignaciones(auth()->user(), $this->filtros());
        $resumen = $this->service()->resumen($asignacionesBase);
        $asignaciones = $this->estadoFiltro
            ? $asignacionesBase->where('estado', $this->estadoFiltro)->values()
            : $asignacionesBase;

        $alumnosVisibles = $this->alumnosVisiblesCaptura();
        $conteosOrigen = [
            'todos' => count($this->alumnos),
            'local' => collect($this->alumnos)->where('foraneo', 'false')->count(),
            'foraneo' => collect($this->alumnos)->where('foraneo', 'true')->count(),
        ];

        $estadoSeleccionado = null;
        $auditoriaReciente = collect();
        if (!empty($this->contextoSeleccionado) && $this->modo === 'materia') {
            $contexto = $this->contextosAutorizadosActuales()->first();
            if ($contexto) {
                $estadoSeleccionado = $this->estadoReal($contexto);
                $auditoriaReciente = CalificacionAuditoria::query()
                    ->with([
                        'usuario:id,username',
                        'alumno:id,matricula,matricula_interna,nombre,apellido_paterno,apellido_materno',
                    ])
                    ->where('asignacion_materia_id', (int) $contexto->asignacion_materia_id)
                    ->where('licenciatura_id', (int) $contexto->licenciatura_id)
                    ->where('modalidad_id', (int) $contexto->modalidad_id)
                    ->where('generacion_id', (int) $contexto->generacion_id)
                    ->where('cuatrimestre_id', (int) $contexto->cuatrimestre_id)
                    ->latest('id')
                    ->limit(30)
                    ->get();
            }
        }

        $coincidenciasAlumno = $this->service()->buscarAlumnosAutorizados(
            auth()->user(), $this->buscarAlumnoGlobal, $this->filtros()
        );
        $alumnoHistorico = null;
        $auditoriaHistorica = collect();
        if ($this->modo === 'historial' && $this->alumnoHistoricoId && $this->puedeAdministrar()) {
            $alumnoHistorico = $this->historico()->alumno(auth()->user(), $this->alumnoHistoricoId);
            $auditoriaHistorica = CalificacionAuditoria::query()
                ->with('usuario:id,username')
                ->where('alumno_id', $this->alumnoHistoricoId)
                ->where('origen', 'historico')
                ->latest('id')->limit(30)->get();
        }
        $coincidenciasHistoricas = $this->puedeAdministrar()
            ? $this->historico()->buscarAlumnos(auth()->user(), $this->buscarAlumnoHistorico)
            : collect();
        $diagnosticoAlumno = null;
        if ($this->modo === 'alumno' && $this->alumnoSeleccionadoId && !empty($this->contextoSeleccionado)) {
            $cuatri = (int) ($this->contextoSeleccionado['cuatrimestre'] ?? 0);
            $diagnosticoAlumno = $this->service()->diagnosticoAlumno($this->alumnoSeleccionadoId, $cuatri);
        }

        if ($this->modo === 'alumno' && $this->alumnoSeleccionadoId && !empty($this->contextoSeleccionado)) {
            $auditoriaReciente = CalificacionAuditoria::query()
                ->with(['usuario:id,username', 'alumno:id,matricula,matricula_interna,nombre,apellido_paterno,apellido_materno'])
                ->where('alumno_id', $this->alumnoSeleccionadoId)
                ->whereIn('asignacion_materia_id', collect($this->asignacionesMatriz)->pluck('asignacion_materia_id')->all())
                ->where('licenciatura_id', (int) $this->contextoSeleccionado['licenciatura_id'])
                ->where('modalidad_id', (int) $this->contextoSeleccionado['modalidad_id'])
                ->where('generacion_id', (int) $this->contextoSeleccionado['generacion_id'])
                ->where('cuatrimestre_id', (int) $this->contextoSeleccionado['cuatrimestre_id'])
                ->latest('id')->limit(30)->get();
        }

        return view('livewire.admin.calificaciones-docente.panel', [
            'opciones' => $opciones,
            'asignaciones' => $asignaciones,
            'resumen' => $resumen,
            'alumnosVisibles' => $alumnosVisibles,
            'conteosOrigen' => $conteosOrigen,
            'puedeAdministrar' => $this->puedeAdministrar(),
            'esProfesorRestringido' => $this->esProfesorRestringido(),
            'estadoSeleccionado' => $estadoSeleccionado,
            'auditoriaReciente' => $auditoriaReciente,
            'coincidenciasAlumno' => $coincidenciasAlumno,
            'diagnosticoAlumno' => $diagnosticoAlumno,
            'alumnoHistorico' => $alumnoHistorico,
            'auditoriaHistorica' => $auditoriaHistorica,
            'coincidenciasHistoricas' => $coincidenciasHistoricas,
        ]);
    }

    private function alumnosVisiblesCaptura(): Collection
    {
        $needle = Str::lower(trim($this->buscarAlumno));
        return collect($this->alumnos)->filter(function (array $a) use ($needle) {
            if ($this->origenFiltro === 'local' && ($a['foraneo'] ?? 'false') !== 'false') return false;
            if ($this->origenFiltro === 'foraneo' && ($a['foraneo'] ?? 'false') !== 'true') return false;
            if ($this->pendientesFiltro !== 'todos') {
                $notas = collect($this->calificacionesGuardadas[$a['id']] ?? [])
                    ->filter(fn ($v) => $this->calificacionValida($this->normalizarCalificacion($v)));
                if ($this->pendientesFiltro === 'pendientes' && $notas->count() >= count($this->asignacionesMatriz)) return false;
                if ($this->pendientesFiltro === 'reprobadas' && !$notas->contains(fn ($v) => is_numeric($v) && (float) $v < 6)) return false;
            }
            if ($needle === '') return true;

            $texto = Str::lower(trim(collect([
                $a['matricula'] ?? '', $a['matricula_interna'] ?? '', $a['nombre'] ?? '',
                $a['apellido_paterno'] ?? '', $a['apellido_materno'] ?? '',
            ])->implode(' ')));
            return str_contains($texto, $needle);
        })->values();
    }

    private function cargarAlumnosYCalificaciones(Collection $contextos, ?int $soloAlumnoId = null): void
    {
        $principal = $contextos->first();
        $alumnos = $this->service()->alumnosParaContexto($principal);
        if ($soloAlumnoId !== null) {
            $alumnos = $alumnos->where('id', $soloAlumnoId)->values();
        }
        $this->alumnos = $alumnos->map(fn ($a) => [
            'id' => (int) $a->id,
            'matricula' => $a->matricula,
            'matricula_interna' => $a->matricula_interna,
            'nombre' => $a->nombre,
            'apellido_paterno' => $a->apellido_paterno,
            'apellido_materno' => $a->apellido_materno,
            'foraneo' => $a->foraneo,
        ])->all();

        $this->calificaciones = [];
        $alumnoIds = $alumnos->pluck('id')->all();

        foreach ($contextos as $contexto) {
            $guardadas = $this->calificacionesActuales($contexto, $alumnoIds);
            foreach ($alumnoIds as $alumnoId) {
                $this->calificaciones[$alumnoId][$contexto->asignacion_materia_id] = $guardadas[$alumnoId] ?? null;
            }
        }
        $this->calificacionesGuardadas = $this->calificaciones;
    }

    private function recargarCapturaActual(): void
    {
        if (empty($this->contextoSeleccionado)) {
            return;
        }

        if ($this->modo === 'matriz') {
            $this->abrirMatriz();
            return;
        }

        if ($this->modo === 'alumno' && $this->alumnoSeleccionadoId) {
            $this->abrirAlumno(
                $this->alumnoSeleccionadoId,
                (int) $this->contextoSeleccionado['asignacion_materia_id'],
                (int) $this->contextoSeleccionado['generacion_id']
            );
            return;
        }

        $this->cargarMateria(
            (int) $this->contextoSeleccionado['asignacion_materia_id'],
            (int) $this->contextoSeleccionado['generacion_id']
        );
    }

    private function contextosAutorizadosActuales(): Collection
    {
        return collect($this->asignacionesMatriz)->map(function ($item) {
            return $this->service()->contextoAsignacion(
                auth()->user(),
                (int) $item['asignacion_materia_id'],
                (int) $item['generacion_id']
            );
        })->filter(function ($contexto) {
            if (empty($this->contextoSeleccionado)) return false;
            foreach (['licenciatura_id', 'modalidad_id', 'generacion_id', 'cuatrimestre_id'] as $campo) {
                if ((int) $contexto->{$campo} !== (int) $this->contextoSeleccionado[$campo]) return false;
            }
            if ($this->modo === 'materia' &&
                (int) $contexto->asignacion_materia_id !== (int) $this->contextoSeleccionado['asignacion_materia_id']) return false;
            return true;
        })->values();
    }

    private function calificacionesActuales(object $contexto, array $alumnoIds): array
    {
        if (!$alumnoIds) {
            return [];
        }

        return Calificacion::query()
            ->whereIn('alumno_id', $alumnoIds)
            ->where('asignacion_materia_id', (int) $contexto->asignacion_materia_id)
            ->where('modalidad_id', (int) $contexto->modalidad_id)
            ->where('generacion_id', (int) $contexto->generacion_id)
            ->where('licenciatura_id', (int) $contexto->licenciatura_id)
            ->where('cuatrimestre_id', (int) $contexto->cuatrimestre_id)
            ->orderBy('id')
            ->get()
            ->groupBy('alumno_id')
            ->map(fn ($items) => $items->last()->calificacion)
            ->all();
    }

    private function queryCalificacion(int $alumnoId, object $contexto)
    {
        return Calificacion::query()
            ->where('alumno_id', $alumnoId)
            ->where('asignacion_materia_id', (int) $contexto->asignacion_materia_id)
            ->where('modalidad_id', (int) $contexto->modalidad_id)
            ->where('generacion_id', (int) $contexto->generacion_id)
            ->where('licenciatura_id', (int) $contexto->licenciatura_id)
            ->where('cuatrimestre_id', (int) $contexto->cuatrimestre_id);
    }

    private function guardarUnaCalificacion(int $alumnoId, object $contexto, string $nuevo, string $origen): bool
    {
        abort_unless($this->calificacionValida($nuevo), 422);

        $alumnoValido = Inscripcion::query()
            ->whereKey($alumnoId)
            ->where('status', 'true')
            ->where('licenciatura_id', (int) $contexto->licenciatura_id)
            ->where('modalidad_id', (int) $contexto->modalidad_id)
            ->where('generacion_id', (int) $contexto->generacion_id)
            ->exists();
        abort_unless($alumnoValido, 403);

        $existente = $this->queryCalificacion($alumnoId, $contexto)->orderByDesc('id')->first();
        $anterior = $this->normalizarCalificacion($existente?->calificacion);
        if ($anterior === $nuevo) {
            return false;
        }

        if ($existente) {
            $existente->update([
                'profesor_id' => (int) $contexto->profesor_id,
                'calificacion' => $nuevo,
            ]);
            $calificacion = $existente;
            $accion = 'actualizada';
        } else {
            $calificacion = Calificacion::create([
                'alumno_id' => $alumnoId,
                'asignacion_materia_id' => (int) $contexto->asignacion_materia_id,
                'modalidad_id' => (int) $contexto->modalidad_id,
                'generacion_id' => (int) $contexto->generacion_id,
                'licenciatura_id' => (int) $contexto->licenciatura_id,
                'cuatrimestre_id' => (int) $contexto->cuatrimestre_id,
                'profesor_id' => (int) $contexto->profesor_id,
                'calificacion' => $nuevo,
            ]);
            $accion = 'creada';
        }

        CalificacionAuditoria::create([
            'calificacion_id' => $calificacion->id,
            'alumno_id' => $alumnoId,
            'asignacion_materia_id' => (int) $contexto->asignacion_materia_id,
            'profesor_id' => (int) $contexto->profesor_id,
            'licenciatura_id' => (int) $contexto->licenciatura_id,
            'modalidad_id' => (int) $contexto->modalidad_id,
            'generacion_id' => (int) $contexto->generacion_id,
            'cuatrimestre_id' => (int) $contexto->cuatrimestre_id,
            'user_id' => auth()->id(),
            'valor_anterior' => $anterior,
            'valor_nuevo' => $nuevo,
            'origen' => $origen,
            'accion' => $accion,
            'detalle' => [
                'licenciatura_id' => (int) $contexto->licenciatura_id,
                'modalidad_id' => (int) $contexto->modalidad_id,
                'generacion_id' => (int) $contexto->generacion_id,
                'cuatrimestre_id' => (int) $contexto->cuatrimestre_id,
            ],
        ]);

        return true;
    }

    private function analizarFilaImportacion(
        string $hoja,
        int $fila,
        int $alumnoId,
        int $asignacionId,
        int $generacionId,
        string $identificador,
        mixed $valor
    ): array {
        $base = [
            'hoja' => $hoja,
            'fila' => $fila,
            'alumno_id' => $alumnoId,
            'asignacion_materia_id' => $asignacionId,
            'generacion_id' => $generacionId,
            'identificador' => trim($identificador),
            'alumno' => '',
            'materia' => '',
            'anterior' => null,
            'nuevo' => $this->normalizarCalificacion($valor),
            'accion' => 'error',
            'mensaje' => '',
        ];

        if (!$alumnoId || !$asignacionId || !$generacionId) {
            $base['mensaje'] = 'Identificadores internos incompletos. Descarga nuevamente la plantilla.';
            return $base;
        }

        $contexto = $this->service()->contextoAsignacion(auth()->user(), $asignacionId, $generacionId);
        if (!$contexto) {
            $base['mensaje'] = 'La materia no está autorizada para este usuario o ya no existe en horarios.';
            return $base;
        }
        $base['materia'] = $contexto->materia;

        if ($this->bloqueadaParaDocente($contexto)) {
            $base['mensaje'] = 'La captura de esta materia está bloqueada.';
            return $base;
        }

        $alumno = Inscripcion::query()
            ->whereKey($alumnoId)
            ->where('status', 'true')
            ->where('licenciatura_id', (int) $contexto->licenciatura_id)
            ->where('modalidad_id', (int) $contexto->modalidad_id)
            ->where('generacion_id', (int) $contexto->generacion_id)
            ->first();

        if (!$alumno) {
            $base['mensaje'] = 'El alumno no pertenece al grupo activo de esta materia.';
            return $base;
        }

        $base['alumno'] = trim("{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombre}");
        $identificador = trim($identificador);
        $coincideInterno = trim((string) $alumno->matricula_interna) !== ''
            && trim((string) $alumno->matricula_interna) === $identificador;
        $coincideSeg = trim((string) $alumno->matricula) !== ''
            && trim((string) $alumno->matricula) === $identificador;

        if (!$coincideInterno && !$coincideSeg) {
            $base['mensaje'] = 'El identificador de la plantilla no coincide con el alumno_id. No se importará.';
            return $base;
        }

        if (!$this->calificacionValida($base['nuevo'])) {
            $base['mensaje'] = 'Calificación inválida. Usa 5 a 10 o NP.';
            return $base;
        }

        $existente = $this->queryCalificacion($alumnoId, $contexto)->orderByDesc('id')->first();
        $anterior = $this->normalizarCalificacion($existente?->calificacion);
        $base['anterior'] = $anterior;

        if ($anterior === $base['nuevo']) {
            $base['accion'] = 'sin_cambio';
            $base['mensaje'] = 'Sin cambios.';
        } elseif ($existente) {
            $base['accion'] = 'actualizar';
            $base['mensaje'] = 'Se actualizará la calificación existente.';
        } else {
            $base['accion'] = 'crear';
            $base['mensaje'] = 'Se registrará una nueva calificación.';
        }

        return $base;
    }

    private function capturaRegistro(object $contexto): CalificacionDocenteCaptura
    {
        $registro = CalificacionDocenteCaptura::firstOrCreate([
            'asignacion_materia_id' => (int) $contexto->asignacion_materia_id,
            'licenciatura_id' => (int) $contexto->licenciatura_id,
            'modalidad_id' => (int) $contexto->modalidad_id,
            'generacion_id' => (int) $contexto->generacion_id,
            'cuatrimestre_id' => (int) $contexto->cuatrimestre_id,
        ], [
            'profesor_id' => (int) $contexto->profesor_id,
            'estado' => 'sin_captura',
        ]);

        if ((int) $registro->profesor_id !== (int) $contexto->profesor_id) {
            $registro->update(['profesor_id' => (int) $contexto->profesor_id]);
        }

        return $registro;
    }

    private function actualizarEstadoCalculado(object $contexto): void
    {
        $registro = $this->capturaRegistro($contexto);
        if (in_array($registro->estado, ['entregada', 'validada'], true)) {
            return;
        }

        $estado = $this->estadoReal($contexto);
        $registro->update(['estado' => $estado['estado']]);
    }

    private function estadoReal(object $contexto): array
    {
        $alumnoIdsActivos = $this->service()->alumnosParaContexto($contexto)->pluck('id');
        $total = $alumnoIdsActivos->count();
        $valores = $alumnoIdsActivos->isEmpty()
            ? collect()
            : $this->queryCalificacionesContexto($contexto)
                ->whereIn('alumno_id', $alumnoIdsActivos->all())
                ->pluck('calificacion')
                ->map(fn ($v) => $this->normalizarCalificacion($v));
        $capturadas = $valores->filter(fn ($v) => $this->calificacionValida($v))->count();
        $registro = CalificacionDocenteCaptura::query()
            ->where('asignacion_materia_id', (int) $contexto->asignacion_materia_id)
            ->where('licenciatura_id', (int) $contexto->licenciatura_id)
            ->where('modalidad_id', (int) $contexto->modalidad_id)
            ->where('generacion_id', (int) $contexto->generacion_id)
            ->where('cuatrimestre_id', (int) $contexto->cuatrimestre_id)
            ->first();

        $estado = match (true) {
            $registro?->estado === 'validada' => 'validada',
            $registro?->estado === 'entregada' => 'entregada',
            $total > 0 && $capturadas >= $total => 'completa',
            $capturadas > 0 => 'parcial',
            default => 'sin_captura',
        };

        return [
            'estado' => $estado,
            'total' => $total,
            'capturadas' => min($total, $capturadas),
            'pendientes' => max(0, $total - $capturadas),
            'fecha_limite' => $registro?->fecha_limite?->format('Y-m-d'),
            'bloqueada_docente' => in_array($estado, ['entregada', 'validada'], true)
                || ($registro?->fecha_limite && now()->startOfDay()->gt($registro->fecha_limite->startOfDay())),
        ];
    }

    private function queryCalificacionesContexto(object $contexto)
    {
        return Calificacion::query()
            ->where('asignacion_materia_id', (int) $contexto->asignacion_materia_id)
            ->where('modalidad_id', (int) $contexto->modalidad_id)
            ->where('generacion_id', (int) $contexto->generacion_id)
            ->where('licenciatura_id', (int) $contexto->licenciatura_id)
            ->where('cuatrimestre_id', (int) $contexto->cuatrimestre_id);
    }

    private function bloqueadaParaDocente(object $contexto): bool
    {
        $estado = $this->estadoReal($contexto);

        // Entregada/validada exige una reapertura explícita incluso para Admin.
        if (in_array($estado['estado'], ['entregada', 'validada'], true)) {
            return true;
        }

        // La fecha límite bloquea al docente, pero Control Escolar conserva override.
        return !$this->puedeAdministrar() && (bool) $estado['bloqueada_docente'];
    }

    private function cargarFechaLimite(): void
    {
        $contexto = $this->contextosAutorizadosActuales()->first();
        if (!$contexto) {
            $this->fechaLimite = null;
            return;
        }

        $registro = CalificacionDocenteCaptura::query()
            ->where('asignacion_materia_id', (int) $contexto->asignacion_materia_id)
            ->where('generacion_id', (int) $contexto->generacion_id)
            ->first();
        $this->fechaLimite = $registro?->fecha_limite?->format('Y-m-d');
    }

    private function filtros(): array
    {
        return [
            'profesor_id' => $this->profesorId,
            'licenciatura_id' => $this->licenciaturaId,
            'modalidad_id' => $this->modalidadId,
            'generacion_id' => $this->generacionId,
            'cuatrimestre_id' => $this->cuatrimestreId,
        ];
    }

    private function normalizarCalificacion(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $v = trim((string) $valor);
        if ($v === '') {
            return null;
        }
        if (strtoupper($v) === 'NP') {
            return 'NP';
        }

        $v = str_replace(',', '.', $v);
        if (is_numeric($v)) {
            $n = (float) $v;
            return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
        }

        return strtoupper($v);
    }

    private function calificacionValida(?string $valor): bool
    {
        if ($valor === null) {
            return false;
        }
        if ($valor === 'NP') {
            return true;
        }
        return is_numeric($valor) && (float) $valor >= 5 && (float) $valor <= 10;
    }

    private function normalizarEncabezado(string $valor): string
    {
        $valor = Str::ascii(Str::lower(trim($valor)));
        return str_replace([' ', '.', '-'], ['_', '', '_'], $valor);
    }

    private function nombreArchivo(string $prefijo, object $contexto): string
    {
        $partes = [
            $prefijo,
            $contexto->materia_clave ?: $contexto->materia,
            $contexto->generacion,
            $contexto->cuatrimestre . 'CUAT',
        ];

        return collect($partes)
            ->map(fn ($p) => Str::upper(Str::slug((string) $p, '_')))
            ->filter()
            ->implode('_');
    }

    private function limpiarImportacion(bool $limpiarArchivo = true): void
    {
        if ($limpiarArchivo) {
            $this->archivoExcel = null;
        }
        $this->vistaPreviaImportacion = [];
        $this->resumenImportacion = [];
        $this->resetValidation('archivoExcel');
    }

    private function puedeAdministrar(): bool
    {
        return auth()->user()->hasAnyRole(['SuperAdmin', 'Admin']);
    }

    private function esProfesorRestringido(): bool
    {
        return $this->service()->esProfesorRestringido(auth()->user());
    }

    private function historico(): CalificacionesHistoricasService
    {
        return app(CalificacionesHistoricasService::class);
    }

    private function service(): CalificacionesDocenteService
    {
        return app(CalificacionesDocenteService::class);
    }

    private function swal(string $icon, string $title): void
    {
        $this->dispatch('swal', [
            'icon' => $icon,
            'title' => $title,
            'position' => 'top-end',
        ]);
    }
}
