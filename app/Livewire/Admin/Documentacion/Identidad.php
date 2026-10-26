<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\DescargaExpedienteIdentidad;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Services\DocumentosIdentidad\DocumentoIdentidadService;
use App\Services\DocumentosIdentidad\ExpedienteIdentidadExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class Identidad extends Component
{
    public string $query = '';

    // Se descarga un máximo de 50 alumnos por petición HTTP.
    public int $loteExportacion = 1;
    public string $firmaLoteExportacion = '';
    public int $totalLotesExportacion = 0;

    public array $alumnos = [];

    public int $selectedIndex = 0;

    public ?array $selectedAlumno = null;

    public ?int $selectedLicenciatura = null;

    public ?int $selectedGeneracion = null;

    public string $estado = 'activos';

    public array $licenciaturas = [];

    public array $generaciones = [];

    public array $todasGeneraciones = [];

    public array $documentosResumen = [];

    public array $pendientes = [];

    public int $documentosEntregados = 0;

    public int $documentosTotales = 0;

    public int $obligatoriosEntregados = 0;

    public int $obligatoriosTotales = 0;

    public int $porcentaje = 0;

    public bool $mostrarSoloPendientes = false;

    public int $porcentajeObligatorios = 0;

    public bool $tieneDocumentos = false;

    public bool $tieneDocumentosExportables = false;

    public bool $organizacionPendiente = false;

    public int $paginasSinClasificar = 0;

    public int $fuentesDocumentales = 0;

    public string $tipoExportacion = 'generacion';

    public array $exportLicenciaturas = [];

    public array $exportGeneraciones = [];

    public array $exportEstados = ['activos'];

    public array $exportacionesRecientes = [];

    public bool $hayExportacionesPendientes = false;

    public function mount(): void
    {
        $this->licenciaturas = Licenciatura::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->toArray();

        $this->todasGeneraciones = Generacion::query()
            ->orderByDesc('generacion')
            ->get(['id', 'generacion', 'activa'])
            ->toArray();

        $this->cargarGeneraciones();
        $this->cargarExportacionesRecientes();
    }

    public function updatedSelectedLicenciatura(): void
    {
        $this->selectedGeneracion = null;
        $this->cargarGeneraciones();
        $this->reiniciarSeleccion();
    }

    public function updatedSelectedGeneracion(): void
    {
        $this->reiniciarSeleccion();
    }

    public function updatedEstado(): void
    {
        if (! in_array($this->estado, ['activos', 'egresados', 'bajas', 'todos'], true)) {
            $this->estado = 'activos';
        }

        $this->reiniciarSeleccion();
    }

    public function updatedQuery(): void
    {
        if ($this->selectedAlumno && $this->query !== $this->textoAlumno($this->selectedAlumno)) {
            $this->selectedAlumno = null;
            $this->limpiarResumen();
        }

        $this->buscarAlumnos();
    }

    public function buscarAlumnos(): void
    {
        $texto = trim($this->query);

        if (mb_strlen($texto) < 2) {
            $this->alumnos = [];
            $this->selectedIndex = 0;

            return;
        }

        Gate::authorize('documentos-identidad.ver');

        $this->alumnos = $this->consultaAlumnosFiltrada()
            ->with($this->relacionesAlumno())
            ->where(function (Builder $q) use ($texto): void {
                // Los comodines escritos en el buscador no deben convertirse en filtros globales.
                $patron = '%'.addcslashes($texto, '%_\\').'%';
                $q->where('nombre', 'like', $patron)
                    ->orWhere('apellido_paterno', 'like', $patron)
                    ->orWhere('apellido_materno', 'like', $patron)
                    ->orWhere('CURP', 'like', $patron)
                    ->orWhere('matricula', 'like', $patron)
                    ->orWhere('matricula_interna', 'like', $patron)
                    ->orWhere('folio', 'like', $patron);

                // Permite localizar nombres completos separados por espacios,
                // aunque cada parte esté en una columna distinta. Compatible con MySQL y SQLite.
                $palabras = array_slice(preg_split('/\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5);
                if (count($palabras) > 1) {
                    $q->orWhere(function (Builder $nombreCompleto) use ($palabras): void {
                        foreach ($palabras as $palabra) {
                            $patronParte = '%'.addcslashes($palabra, '%_\\').'%';
                            $nombreCompleto->where(function (Builder $parte) use ($patronParte): void {
                                $parte->where('nombre', 'like', $patronParte)
                                    ->orWhere('apellido_paterno', 'like', $patronParte)
                                    ->orWhere('apellido_materno', 'like', $patronParte);
                            });
                        }
                    });
                }
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->limit(12)
            ->get()
            ->toArray();

        $this->selectedIndex = 0;
    }

    public function selectAlumno(int $index): void
    {
        if (! isset($this->alumnos[$index]['id'])) {
            $this->dispatch('swal', title: 'Alumno no encontrado', icon: 'error', position: 'top');

            return;
        }

        $this->seleccionarAlumnoPorId((int) $this->alumnos[$index]['id']);
    }

    /**
     * Utiliza la clave del alumno, nunca la posición visible en un listado dinámico.
     * Los filtros se verifican nuevamente contra MySQL antes de mostrar el expediente.
     */
    public function seleccionarAlumnoPorId(int $id): void
    {
        Gate::authorize('documentos-identidad.ver');

        if ($id < 1 || ! in_array($id, array_map('intval', array_column($this->alumnos, 'id')), true)) {
            $this->dispatch('swal', title: 'Selecciona un alumno del listado actual', icon: 'warning', position: 'top');

            return;
        }

        $alumno = $this->consultaAlumnosFiltrada()
            ->with($this->relacionesAlumno())
            ->find($id);

        if (! $alumno) {
            $this->dispatch('swal', title: 'El alumno no coincide con los filtros', icon: 'warning', position: 'top');

            return;
        }

        $this->selectedAlumno = $alumno->toArray();
        $this->query = $this->textoAlumno($this->selectedAlumno);
        $this->alumnos = [];
        $this->selectedIndex = 0;
        $this->mostrarSoloPendientes = false;
        $this->actualizarResumen();
    }

    public function limpiarAlumno(): void
    {
        $this->reiniciarSeleccion();
    }

    public function cambiarVistaDocumentos(bool $soloPendientes): void
    {
        $this->mostrarSoloPendientes = $soloPendientes;
    }

    /** @return array<int, string> */
    protected function relacionesAlumno(): array
    {
        return [
            'licenciatura:id,nombre',
            'generacion:id,generacion,activa',
            'modalidad:id,nombre',
            'cuatrimestre:id,nombre_cuatrimestre,cuatrimestre',
        ];
    }

    protected function consultaAlumnosFiltrada(): Builder
    {
        return Inscripcion::query()
            ->when($this->selectedLicenciatura, fn (Builder $q, int $id) => $q->where('licenciatura_id', $id))
            ->when($this->selectedGeneracion, fn (Builder $q, int $id) => $q->where('generacion_id', $id))
            ->when($this->estado === 'activos', fn (Builder $q) => $q
                ->where('status', 'true')
                ->where(function (Builder $estadoQuery): void {
                    $estadoQuery->whereNull('egresado')->orWhere('egresado', 'false');
                }))
            ->when($this->estado === 'egresados', fn (Builder $q) => $q->where('egresado', 'true'))
            ->when($this->estado === 'bajas', fn (Builder $q) => $q
                ->where('status', 'false')
                ->where(function (Builder $estadoQuery): void {
                    $estadoQuery->whereNull('egresado')->orWhere('egresado', 'false');
                }));
    }

    #[On('documento-identidad-actualizado')]
    public function documentoActualizado(int $inscripcionId): void
    {
        if (($this->selectedAlumno['id'] ?? null) !== $inscripcionId) {
            return;
        }

        $this->actualizarResumen();
    }

    #[On('organizacion-identidad-confirmada')]
    public function organizacionConfirmada(int $inscripcionId): void
    {
        $this->documentoActualizado($inscripcionId);
    }

    #[On('organizacion-identidad-borrador-actualizado')]
    public function organizacionBorradorActualizado(int $inscripcionId): void
    {
        $this->documentoActualizado($inscripcionId);
    }

    public function abrirOrganizadorGeneral(): void
    {
        if (! $this->selectedAlumno) {
            return;
        }

        abort_unless(
            Gate::allows('documentos-identidad.reemplazar') || Gate::allows('documentos-identidad.subir'),
            403
        );

        $this->dispatch(
            'abrir-organizador-identidad',
            inscripcionId: (int) $this->selectedAlumno['id'],
            fuenteId: null
        );
    }

    public function selectIndexUp(): void
    {
        if ($this->alumnos !== []) {
            $this->selectedIndex = ($this->selectedIndex - 1 + count($this->alumnos)) % count($this->alumnos);
        }
    }

    public function selectIndexDown(): void
    {
        if ($this->alumnos !== []) {
            $this->selectedIndex = ($this->selectedIndex + 1) % count($this->alumnos);
        }
    }

    public function prepararDescarga(): void
    {
        Gate::authorize('documentos-identidad.descargar');

        $licenciatura = $this->selectedLicenciatura ?: ($this->selectedAlumno['licenciatura_id'] ?? null);
        $generacion = $this->selectedGeneracion ?: ($this->selectedAlumno['generacion_id'] ?? null);
        $this->exportLicenciaturas = $licenciatura ? [(int) $licenciatura] : [];
        $this->exportGeneraciones = $generacion ? [(int) $generacion] : [];
        $this->exportEstados = $this->estado === 'todos'
            ? ['activos', 'egresados', 'bajas']
            : [$this->estado];

        $this->tipoExportacion = $this->selectedAlumno
            ? 'alumno'
            : ($this->selectedGeneracion ? 'generacion' : ($this->selectedLicenciatura ? 'licenciatura' : 'generacion'));

        $this->loteExportacion = 1;
        $this->firmaLoteExportacion = '';
        $this->totalLotesExportacion = 0;
        $this->resetValidation();
        $this->cargarExportacionesRecientes();
    }

    public function seleccionarTodasLicenciaturas(): void
    {
        $this->exportLicenciaturas = collect($this->licenciaturas)->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }

    public function seleccionarTodasGeneraciones(): void
    {
        $this->exportGeneraciones = collect($this->todasGeneraciones)->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }

    public function limpiarLicenciaturasExportacion(): void
    {
        $this->exportLicenciaturas = [];
    }

    public function limpiarGeneracionesExportacion(): void
    {
        $this->exportGeneraciones = [];
    }

    public function solicitarExportacion(ExpedienteIdentidadExportService $service): void
    {
        Gate::authorize('documentos-identidad.descargar');

        if ($this->tipoExportacion === 'alumno') {
            if (! $this->selectedAlumno) {
                $this->addError('tipoExportacion', 'Primero selecciona un alumno en el buscador.');
            }

            return;
        }

        $this->validate([
            'tipoExportacion' => ['required', 'in:generacion,licenciatura'],
            'exportLicenciaturas' => ['array'],
            'exportLicenciaturas.*' => ['integer', 'exists:licenciaturas,id'],
            'exportGeneraciones' => ['array'],
            'exportGeneraciones.*' => ['integer', 'exists:generaciones,id'],
            'exportEstados' => ['required', 'array', 'min:1'],
            'exportEstados.*' => ['in:activos,egresados,bajas'],
        ]);

        if ($this->tipoExportacion === 'generacion' && $this->exportGeneraciones === []) {
            $this->addError('exportGeneraciones', 'Selecciona al menos una generación.');

            return;
        }

        if ($this->tipoExportacion === 'licenciatura' && $this->exportLicenciaturas === []) {
            $this->addError('exportLicenciaturas', 'Selecciona al menos una licenciatura.');

            return;
        }

        $filtros = [
            'licenciaturas' => array_values(array_unique(array_map('intval', $this->exportLicenciaturas))),
            'generaciones' => array_values(array_unique(array_map('intval', $this->exportGeneraciones))),
            'estados' => array_values(array_unique($this->exportEstados)),
        ];

        $total = $service->contarAlumnos($filtros);

        if ($total < 1) {
            $this->dispatch('swal',
                title: 'Sin alumnos para exportar',
                text: 'No hay alumnos que coincidan con los filtros seleccionados.',
                icon: 'warning',
                position: 'top'
            );

            return;
        }

        // El lote es una partición estable de la misma selección y nunca supera 50 alumnos.
        $firma = hash('sha256', json_encode([$this->tipoExportacion, $filtros]));
        if ($this->firmaLoteExportacion !== $firma) {
            $this->loteExportacion = 1;
            $this->firmaLoteExportacion = $firma;
        }

        $totalLotes = (int) ceil($total / 50);
        $this->totalLotesExportacion = $totalLotes;
        $loteActual = max(1, min($this->loteExportacion, $totalLotes));
        $ids = (clone $service->consultaAlumnos($filtros))
            ->offset(($loteActual - 1) * 50)
            ->limit(50)
            ->pluck('inscripciones.id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if ($ids === []) {
            $this->dispatch('swal', title: 'Sin alumnos en este lote', text: 'Actualiza los filtros e inténtalo de nuevo.', icon: 'warning', position: 'top');
            return;
        }
        $filtros['ids_lote'] = $ids;
        $filtros['numero_lote'] = $totalLotes > 1 ? $loteActual : 0;
        $totalDelLote = count($ids);

        $descarga = DescargaExpedienteIdentidad::query()->create([
            'usuario_id' => auth()->id(),
            'tipo' => $this->tipoExportacion,
            'formato' => 'zip',
            'estado' => 'pendiente',
            'filtros' => $filtros,
            'total_alumnos' => $totalDelLote,
            'ip' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
            'solicitado_at' => now(),
        ]);

        try {
            // Descarga directa solicitada: nunca despachar este ZIP a la cola.
            // Mantener el proceso HTTP activo durante la generación (Laragon local).
            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }

            $service->generarZip($descarga);
            $descarga->refresh();

            if (! $descarga->estaLista()) {
                throw new \RuntimeException('El archivo ZIP no terminó de generarse correctamente.');
            }

            $this->dispatch('swal',
                title: 'Exportación lista',
                text: "Lote {$loteActual}/{$totalLotes}: ZIP con {$totalDelLote} alumno(s) listo. La descarga comenzará automáticamente.",
                icon: 'success',
                position: 'top'
            );

            $this->loteExportacion = $loteActual < $totalLotes ? $loteActual + 1 : 1;

            $this->dispatch('expedientes-zip-descarga-directa',
                url: route('admin.expedientes-identidad.exportacion.descargar', $descarga)
            );
        } catch (Throwable $e) {
            $descarga->forceFill([
                'estado' => 'error',
                'error' => mb_substr($e->getMessage(), 0, 5000),
                'completado_at' => now(),
            ])->save();

            $this->dispatch('swal',
                title: 'No se pudo generar la exportación',
                text: $e->getMessage(),
                icon: 'error',
                position: 'top'
            );
        }

        $this->cargarExportacionesRecientes();
    }

    public function actualizarExportaciones(): void
    {
        $this->cargarExportacionesRecientes();
    }

    protected function cargarGeneraciones(): void
    {
        $this->generaciones = Generacion::query()
            ->when($this->selectedLicenciatura, function (Builder $query, int $licenciaturaId): void {
                $query->whereHas('asignarGeneracion', fn (Builder $q) => $q->where('licenciatura_id', $licenciaturaId));
            })
            ->orderByDesc('generacion')
            ->get(['id', 'generacion', 'activa'])
            ->toArray();
    }

    protected function cargarExportacionesRecientes(): void
    {
        if (! auth()->check()) {
            $this->exportacionesRecientes = [];
            $this->hayExportacionesPendientes = false;

            return;
        }

        $this->exportacionesRecientes = DescargaExpedienteIdentidad::query()
            ->where('usuario_id', auth()->id())
            ->where('formato', 'zip')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (DescargaExpedienteIdentidad $descarga): array => [
                'id' => $descarga->id,
                'tipo' => $descarga->tipo,
                'estado' => $descarga->estado,
                'total_alumnos' => $descarga->total_alumnos,
                'alumnos_procesados' => $descarga->alumnos_procesados,
                'alumnos_incompletos' => $descarga->alumnos_incompletos,
                'documentos_faltantes' => $descarga->documentos_faltantes,
                'archivo_nombre' => $descarga->archivo_nombre,
                'error' => $descarga->error,
                'created_at' => optional($descarga->created_at)->format('d/m/Y H:i'),
                'completado_at' => optional($descarga->completado_at)->format('d/m/Y H:i'),
                'url' => $descarga->estaLista()
                    ? route('admin.expedientes-identidad.exportacion.descargar', $descarga)
                    : null,
            ])
            ->toArray();

        $this->hayExportacionesPendientes = collect($this->exportacionesRecientes)
            ->contains(fn (array $exportacion): bool => in_array($exportacion['estado'], ['pendiente', 'procesando'], true));
    }

    protected function actualizarResumen(): void
    {
        $this->limpiarResumen();

        if (! $this->selectedAlumno) {
            return;
        }

        $service = app(DocumentoIdentidadService::class);
        $alumnoModelo = Inscripcion::query()->findOrFail((int) $this->selectedAlumno['id']);
        $estadoOrganizacion = $service->estadoOrganizacion($alumnoModelo, auth()->id());
        $this->organizacionPendiente = (bool) $estadoOrganizacion['pendiente'];
        $this->paginasSinClasificar = (int) $estadoOrganizacion['paginas_sin_clasificar'];
        $this->fuentesDocumentales = (int) $estadoOrganizacion['fuentes'];
        $disk = $service->disk();
        $tiposExportables = (array) config('documentos_identidad.export.types', [
            'curp',
            'acta_nacimiento',
            'certificado_estudios',
        ]);

        foreach ($service->tipos() as $tipo => $config) {
            $documento = $service->actual((int) $this->selectedAlumno['id'], $tipo);
            $existe = $documento && Storage::disk($disk)->exists($documento->ruta);

            $this->documentosResumen[$tipo] = [
                'label' => $config['label'],
                'obligatorio' => (bool) $config['required'],
                'entregado' => (bool) $existe,
            ];

            $this->documentosTotales++;
            $this->obligatoriosTotales += $config['required'] ? 1 : 0;

            if ($existe) {
                if (in_array($tipo, $tiposExportables, true)) {
                    $this->tieneDocumentosExportables = true;
                }

                $this->documentosEntregados++;
                $this->obligatoriosEntregados += $config['required'] ? 1 : 0;
            } else {
                $this->pendientes[] = $config['label'];
            }
        }

        $this->porcentaje = $this->documentosTotales > 0
            ? (int) round(($this->documentosEntregados / $this->documentosTotales) * 100)
            : 0;
        $this->porcentajeObligatorios = $this->obligatoriosTotales > 0
            ? (int) round(($this->obligatoriosEntregados / $this->obligatoriosTotales) * 100)
            : 100;
        $this->tieneDocumentos = $this->documentosEntregados > 0;
    }

    protected function textoAlumno(array $alumno): string
    {
        return trim(sprintf(
            '%s %s %s - %s - %s',
            $alumno['nombre'] ?? '',
            $alumno['apellido_paterno'] ?? '',
            $alumno['apellido_materno'] ?? '',
            $alumno['matricula'] ?? '',
            $alumno['CURP'] ?? ''
        ));
    }

    protected function reiniciarSeleccion(): void
    {
        $this->query = '';
        $this->alumnos = [];
        $this->selectedIndex = 0;
        $this->selectedAlumno = null;
        $this->mostrarSoloPendientes = false;
        $this->limpiarResumen();
    }

    protected function limpiarResumen(): void
    {
        $this->documentosResumen = [];
        $this->pendientes = [];
        $this->documentosEntregados = 0;
        $this->documentosTotales = 0;
        $this->obligatoriosEntregados = 0;
        $this->obligatoriosTotales = 0;
        $this->porcentaje = 0;
        $this->porcentajeObligatorios = 0;
        $this->tieneDocumentos = false;
        $this->tieneDocumentosExportables = false;
        $this->organizacionPendiente = false;
        $this->paginasSinClasificar = 0;
        $this->fuentesDocumentales = 0;
    }

    public function render()
    {
        return view('livewire.admin.documentacion.identidad');
    }
}
