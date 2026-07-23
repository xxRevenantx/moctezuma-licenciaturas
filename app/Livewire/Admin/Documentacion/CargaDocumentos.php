<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\DocumentoIdentidad;
use App\Models\Inscripcion;
use App\Services\DocumentosIdentidad\DocumentoIdentidadService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class CargaDocumentos extends Component
{
    use WithFileUploads;

    public $archivo;

    public int $inscripcionId;

    public string $tipo;

    public string $label = '';

    public bool $obligatorio = false;

    public bool $guardado = false;

    public bool $inconsistente = false;

    public bool $requiereConfirmacion = false;

    public bool $organizacionPendiente = false;

    public bool $tieneFuentes = false;

    public ?int $documentoId = null;

    public ?string $archivoGuardadoUrl = null;

    public ?string $archivoDescargaUrl = null;

    public string $nombreArchivo = '';

    public string $tamanoArchivo = '';

    public string $mensaje = '';

    public array $historial = [];

    public array $fuentesOriginales = [];

    public int $paginasDocumento = 0;

    public int $archivoPaginas = 0;

    public int $maxMb = 10;

    public function mount(int $inscripcionId, string $tipo): void
    {
        $this->inscripcionId = $inscripcionId;
        $this->tipo = $tipo;

        $config = app(DocumentoIdentidadService::class)->configuracionTipo($tipo);
        $this->label = $config['label'];
        $this->obligatorio = (bool) $config['required'];
        $this->maxMb = max(1, (int) ceil(((int) config('documentos_identidad.max_kb', 10240)) / 1024));

        $this->cargarEstado();
    }

    public function updatedArchivo(): void
    {
        $this->resetErrorBag('archivo');
        $this->mensaje = '';
        $this->requiereConfirmacion = false;
        $this->archivoPaginas = 0;
        $this->validarArchivo();

        try {
            $inspeccion = app(DocumentoIdentidadService::class)->inspeccionarArchivoSubido($this->archivo);
            $this->archivoPaginas = (int) $inspeccion['paginas'];
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->addError('archivo', $e->getMessage());

            return;
        }

        if ($this->guardado) {
            Gate::authorize('documentos-identidad.reemplazar');
            $this->requiereConfirmacion = true;

            return;
        }

        Gate::authorize('documentos-identidad.subir');
        $this->guardarArchivo('agregar');
    }

    public function guardarArchivo(string $modo = 'reemplazar'): void
    {
        $this->validarArchivo();

        if (! in_array($modo, ['agregar', 'reemplazar'], true)) {
            $this->addError('archivo', 'Selecciona una acción válida para el nuevo archivo.');

            return;
        }

        $actual = app(DocumentoIdentidadService::class)->actual($this->inscripcionId, $this->tipo);
        Gate::authorize($actual ? 'documentos-identidad.reemplazar' : 'documentos-identidad.subir');
        $alumno = Inscripcion::query()->findOrFail($this->inscripcionId);

        try {
            $resultado = app(DocumentoIdentidadService::class)->guardarFuenteDesdeSubida(
                $this->archivo,
                $alumno,
                $this->tipo,
                $modo,
                auth()->id()
            );

            $fuenteId = $resultado['fuente']->id;
            $paginas = (int) $resultado['paginas'];
            $autoConfirmado = (bool) $resultado['auto_confirmado'];
            $this->reset('archivo');
            $this->archivoPaginas = 0;
            $this->requiereConfirmacion = false;

            if ($autoConfirmado) {
                $this->mensaje = $modo === 'reemplazar'
                    ? 'Documento reemplazado y confirmado. El archivo anterior se conserva como fuente e historial.'
                    : 'Página agregada y documento confirmado correctamente.';
                $this->dispatch('documento-identidad-actualizado', inscripcionId: $this->inscripcionId);
                $this->dispatch('organizacion-identidad-confirmada', inscripcionId: $this->inscripcionId);
                $this->dispatch('swal', title: 'Documento guardado', text: $this->mensaje, icon: 'success', position: 'top');
            } else {
                $this->mensaje = "El archivo de {$paginas} páginas quedó en el organizador. Confirma qué páginas pertenecen a cada documento.";
                $this->dispatch('organizacion-identidad-borrador-actualizado', inscripcionId: $this->inscripcionId);
                $this->dispatch(
                    'abrir-organizador-identidad',
                    inscripcionId: $this->inscripcionId,
                    fuenteId: $fuenteId
                );
                $this->dispatch('swal', title: 'Organiza las páginas', text: $this->mensaje, icon: 'info', position: 'top');
            }

            $this->cargarEstado(false);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            $this->addError('archivo', $e->getMessage());
        }
    }

    public function cancelarReemplazo(): void
    {
        $this->reset('archivo');
        $this->archivoPaginas = 0;
        $this->requiereConfirmacion = false;
        $this->mensaje = 'Carga cancelada; se conservó el documento actual.';
    }

    public function abrirOrganizador(): void
    {
        abort_unless(
            Gate::allows('documentos-identidad.reemplazar') || Gate::allows('documentos-identidad.subir'),
            403
        );
        $this->dispatch('abrir-organizador-identidad', inscripcionId: $this->inscripcionId, fuenteId: null);
    }

    public function eliminarArchivo(): void
    {
        Gate::authorize('documentos-identidad.eliminar');

        $alumno = Inscripcion::query()->findOrFail($this->inscripcionId);
        app(DocumentoIdentidadService::class)->retirarTipoOrganizado($alumno, $this->tipo, auth()->id());

        $this->reset('archivo');
        $this->archivoPaginas = 0;
        $this->requiereConfirmacion = false;
        $this->mensaje = 'El documento se retiró del expediente. Sus páginas y archivos originales permanecen disponibles en el organizador y el historial.';
        $this->cargarEstado(false);

        $this->dispatch('documento-identidad-actualizado', inscripcionId: $this->inscripcionId);
        $this->dispatch('organizacion-identidad-confirmada', inscripcionId: $this->inscripcionId);
        $this->dispatch('swal', title: 'Documento retirado', icon: 'success', position: 'top');
    }

    #[On('organizacion-identidad-confirmada')]
    public function organizacionConfirmada(int $inscripcionId): void
    {
        $this->organizacionActualizada($inscripcionId);
    }

    #[On('organizacion-identidad-borrador-actualizado')]
    public function organizacionBorradorActualizado(int $inscripcionId): void
    {
        $this->organizacionActualizada($inscripcionId);
    }

    protected function organizacionActualizada(int $inscripcionId): void
    {
        if ($inscripcionId !== $this->inscripcionId) {
            return;
        }

        $this->cargarEstado(false);
    }

    protected function validarArchivo(): void
    {
        $max = (int) config('documentos_identidad.max_kb', 10240);
        $extensiones = implode(',', (array) config('documentos_identidad.allowed_extensions', ['pdf', 'jpg', 'jpeg', 'png']));

        $this->validate([
            'archivo' => "required|file|mimes:{$extensiones}|max:{$max}",
        ], [
            'archivo.required' => 'Selecciona un archivo.',
            'archivo.file' => 'El archivo seleccionado no es válido.',
            'archivo.mimes' => 'Solo se aceptan archivos PDF, JPG y PNG.',
            'archivo.max' => "El archivo no debe superar {$this->maxMb} MB.",
        ]);
    }

    protected function cargarEstado(bool $limpiarMensaje = true): void
    {
        $service = app(DocumentoIdentidadService::class);
        $alumno = Inscripcion::query()->findOrFail($this->inscripcionId);
        $documento = $service->actual($this->inscripcionId, $this->tipo);
        $disk = $service->disk();

        $this->guardado = false;
        $this->inconsistente = false;
        $this->documentoId = null;
        $this->archivoGuardadoUrl = null;
        $this->archivoDescargaUrl = null;
        $this->nombreArchivo = '';
        $this->tamanoArchivo = '';
        $this->paginasDocumento = 0;

        if ($documento) {
            if (Storage::disk($disk)->exists($documento->ruta)) {
                $this->guardado = true;
                $this->documentoId = $documento->id;
                $this->archivoGuardadoUrl = route('admin.documentos-identidad.ver', $documento);
                $this->archivoDescargaUrl = route('admin.documentos-identidad.descargar', $documento);
                $this->nombreArchivo = $documento->nombre_original;
                $this->tamanoArchivo = $this->formatoTamano($documento->tamano);
                $this->paginasDocumento = (int) (($documento->metadatos ?? [])['paginas'] ?? 0);
            } else {
                $this->inconsistente = true;
            }
        }

        $this->fuentesOriginales = $service->fuentesConfirmadasTipo($alumno, $this->tipo);
        $estadoOrganizacion = $service->estadoOrganizacion($alumno, auth()->id());
        $this->organizacionPendiente = (bool) $estadoOrganizacion['pendiente'];
        $this->tieneFuentes = (int) $estadoOrganizacion['fuentes'] > 0;

        $this->historial = DocumentoIdentidad::query()
            ->where('inscripcion_id', $this->inscripcionId)
            ->where('tipo', $this->tipo)
            ->orderByDesc('version')
            ->limit(8)
            ->get()
            ->map(function (DocumentoIdentidad $item) use ($disk): array {
                $existe = Storage::disk($disk)->exists($item->ruta);

                return [
                    'id' => $item->id,
                    'version' => $item->version,
                    'estado' => $item->estado,
                    'nombre' => $item->nombre_original,
                    'tamano' => $this->formatoTamano($item->tamano),
                    'paginas' => (int) (($item->metadatos ?? [])['paginas'] ?? 0),
                    'fecha' => optional($item->created_at)->format('d/m/Y H:i'),
                    'usuario' => $item->usuario?->name ?? 'Sistema',
                    'url' => $existe ? route('admin.documentos-identidad.ver', $item) : null,
                ];
            })
            ->toArray();

        if ($limpiarMensaje) {
            $this->mensaje = '';
        }
    }

    protected function formatoTamano(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        return number_format($bytes / 1024, 1) . ' KB';
    }

    public function render()
    {
        return view('livewire.admin.documentacion.carga-documentos');
    }
}
