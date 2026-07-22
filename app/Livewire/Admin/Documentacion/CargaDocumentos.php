<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\DocumentoIdentidad;
use App\Models\Inscripcion;
use App\Services\DocumentosIdentidad\DocumentoIdentidadService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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

    public ?int $documentoId = null;

    public ?string $archivoGuardadoUrl = null;

    public ?string $archivoDescargaUrl = null;

    public string $nombreArchivo = '';

    public string $tamanoArchivo = '';

    public string $mensaje = '';

    public array $historial = [];

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

        $this->validarArchivo();

        if ($this->guardado) {
            Gate::authorize('documentos-identidad.reemplazar');
            $this->requiereConfirmacion = true;

            return;
        }

        Gate::authorize('documentos-identidad.subir');
        $this->guardarArchivo();
    }

    public function guardarArchivo(bool $confirmado = false): void
    {
        $this->validarArchivo();

        $actual = app(DocumentoIdentidadService::class)->actual($this->inscripcionId, $this->tipo);

        if ($actual && ! $confirmado) {
            Gate::authorize('documentos-identidad.reemplazar');
            $this->requiereConfirmacion = true;

            return;
        }

        Gate::authorize($actual ? 'documentos-identidad.reemplazar' : 'documentos-identidad.subir');

        $alumno = Inscripcion::findOrFail($this->inscripcionId);

        try {
            app(DocumentoIdentidadService::class)->guardarSubida(
                $this->archivo,
                $alumno,
                $this->tipo,
                auth()->id()
            );

            $this->reset('archivo');
            $this->requiereConfirmacion = false;
            $this->mensaje = $actual
                ? 'Documento reemplazado correctamente. La versión anterior quedó en el historial.'
                : 'Documento validado y guardado correctamente.';
            $this->cargarEstado(false);

            $this->dispatch('documento-identidad-actualizado', inscripcionId: $this->inscripcionId);
            $this->dispatch('swal', title: 'Documento guardado', text: $this->mensaje, icon: 'success', position: 'top');
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
        $this->requiereConfirmacion = false;
        $this->mensaje = 'Reemplazo cancelado; se conservó el documento actual.';
    }

    public function eliminarArchivo(): void
    {
        Gate::authorize('documentos-identidad.eliminar');

        $alumno = Inscripcion::findOrFail($this->inscripcionId);
        app(DocumentoIdentidadService::class)->eliminarActual($alumno, $this->tipo, auth()->id());

        $this->reset('archivo');
        $this->requiereConfirmacion = false;
        $this->mensaje = 'El documento se retiró del expediente. El historial permanece disponible para auditoría.';
        $this->cargarEstado(false);

        $this->dispatch('documento-identidad-actualizado', inscripcionId: $this->inscripcionId);
        $this->dispatch('swal', title: 'Documento retirado', icon: 'success', position: 'top');
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
        $documento = $service->actual($this->inscripcionId, $this->tipo);
        $disk = $service->disk();

        $this->guardado = false;
        $this->inconsistente = false;
        $this->documentoId = null;
        $this->archivoGuardadoUrl = null;
        $this->archivoDescargaUrl = null;
        $this->nombreArchivo = '';
        $this->tamanoArchivo = '';

        if ($documento) {
            if (Storage::disk($disk)->exists($documento->ruta)) {
                $this->guardado = true;
                $this->documentoId = $documento->id;
                $this->archivoGuardadoUrl = route('admin.documentos-identidad.ver', $documento);
                $this->archivoDescargaUrl = route('admin.documentos-identidad.descargar', $documento);
                $this->nombreArchivo = $documento->nombre_original;
                $this->tamanoArchivo = $this->formatoTamano($documento->tamano);
            } else {
                $this->inconsistente = true;
            }
        }

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
