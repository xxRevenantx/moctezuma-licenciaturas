<?php

namespace App\Livewire\Admin\Documentacion;

use App\Jobs\GenerarExpedientesIdentidadZip;
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

        $this->alumnos = Inscripcion::query()
            ->with([
                'licenciatura:id,nombre',
                'generacion:id,generacion,activa',
                'modalidad:id,nombre',
                'cuatrimestre:id,nombre_cuatrimestre,cuatrimestre',
            ])
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
                }))
            ->where(function (Builder $q) use ($texto): void {
                $q->where('nombre', 'like', "%{$texto}%")
                    ->orWhere('apellido_paterno', 'like', "%{$texto}%")
                    ->orWhere('apellido_materno', 'like', "%{$texto}%")
                    ->orWhere('CURP', 'like', "%{$texto}%")
                    ->orWhere('matricula', 'like', "%{$texto}%")
                    ->orWhere('folio', 'like', "%{$texto}%");
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->limit(10)
            ->get()
            ->toArray();

        $this->selectedIndex = 0;
    }

    public function selectAlumno(int $index): void
    {
        if (! isset($this->alumnos[$index])) {
            $this->dispatch('swal', title: 'Alumno no encontrado', icon: 'error', position: 'top');

            return;
        }

        $alumno = Inscripcion::query()
            ->with([
                'licenciatura:id,nombre',
                'generacion:id,generacion,activa',
                'modalidad:id,nombre',
                'cuatrimestre:id,nombre_cuatrimestre,cuatrimestre',
            ])
            ->find($this->alumnos[$index]['id']);

        if (! $alumno) {
            $this->dispatch('swal', title: 'Alumno no encontrado', icon: 'error', position: 'top');

            return;
        }

        $this->selectedAlumno = $alumno->toArray();
        $this->query = $this->textoAlumno($this->selectedAlumno);
        $this->alumnos = [];
        $this->selectedIndex = 0;
        $this->actualizarResumen();
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

        $this->exportLicenciaturas = $this->selectedLicenciatura ? [$this->selectedLicenciatura] : [];
        $this->exportGeneraciones = $this->selectedGeneracion ? [$this->selectedGeneracion] : [];
        $this->exportEstados = $this->estado === 'todos'
            ? ['activos', 'egresados', 'bajas']
            : [$this->estado];

        $this->tipoExportacion = $this->selectedGeneracion
            ? 'generacion'
            : ($this->selectedLicenciatura ? 'licenciatura' : ($this->selectedAlumno ? 'alumno' : 'generacion'));

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

        $descarga = DescargaExpedienteIdentidad::query()->create([
            'usuario_id' => auth()->id(),
            'tipo' => $this->tipoExportacion,
            'formato' => 'zip',
            'estado' => 'pendiente',
            'filtros' => $filtros,
            'total_alumnos' => $total,
            'ip' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
            'solicitado_at' => now(),
        ]);

        $limiteSincrono = max(1, (int) config('documentos_identidad.export.sync_limit', 25));

        try {
            if ($total <= $limiteSincrono) {
                $service->generarZip($descarga);
                $mensaje = "El ZIP con {$total} alumno(s) ya está listo para descargar.";
            } else {
                GenerarExpedientesIdentidadZip::dispatch($descarga->id);
                $mensaje = "La exportación de {$total} alumno(s) se procesará en segundo plano.";
            }

            $this->dispatch('swal',
                title: $total <= $limiteSincrono ? 'Exportación lista' : 'Exportación en proceso',
                text: $mensaje,
                icon: 'success',
                position: 'top'
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
