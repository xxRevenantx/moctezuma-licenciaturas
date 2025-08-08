<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Inscripcion;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Illuminate\Support\Str; // 👈

class CargaDocumentos extends Component
{
    use WithFileUploads;

    public $archivo;
    public $archivoGuardadoUrl = null;
    public $guardado = false;
    public $nombreArchivo = '';
    public $tamanoArchivo = '';
    public $inscripcionId;

    public $label;       // solo UI
    public $wireId;      // 👈 clave para columna BD
    public $rutaGuardado;
    public $mensaje;

    public $estudiante;

    public function mount()
    {
        if ($this->inscripcionId) {
            $this->estudiante = Inscripcion::find($this->inscripcionId);
            $this->cargarArchivoGuardado();
        }
    }

    #[On('alumnoSeleccionado')]
    public function cargarDocumentosPorAlumno($id)
    {
        $this->inscripcionId = $id;
        $this->estudiante = Inscripcion::find($id);
        $this->archivo = null;
        $this->mensaje = null;
        $this->cargarArchivoGuardado();
    }

    public function updatedArchivo()
    {
        $this->validate([
            'archivo' => 'required|file|mimes:pdf|max:1024',
        ]);
        $this->guardado = false;
        $this->mensaje = null;
    }

    public function guardarArchivo()
    {
        $this->validate([
            'archivo' => 'required|file|mimes:pdf|max:1024',
        ]);

        if (!$this->archivo || !$this->inscripcionId) return;

        if (!$this->estudiante) {
            $this->estudiante = Inscripcion::find($this->inscripcionId);
        }

        $nombreGenerado = $this->generarNombrePersonalizado();
        $ruta = $this->rutaGuardado . '/' . $nombreGenerado;

        if (Storage::exists($ruta)) {
            Storage::delete($ruta);
        }

        $this->archivo->storeAs($this->rutaGuardado, $nombreGenerado);

        // ✅ columna correcta desde wireId
        $columna = $this->getColumna();
        if ($columna) {
            $this->estudiante->$columna = $nombreGenerado;
            $this->estudiante->save();
        }

        $this->estudiante->refresh();
        $nombreDesdeBD = $columna ? ($this->estudiante->$columna ?: $nombreGenerado) : $nombreGenerado;

        $rutaFinal = $this->rutaGuardado . '/' . $nombreDesdeBD;
        $this->archivoGuardadoUrl = Storage::url($rutaFinal);
        $this->guardado = true;
        $this->nombreArchivo = $nombreDesdeBD;
        $this->tamanoArchivo = $this->formatoTamano(Storage::size($rutaFinal));
        $this->mensaje = 'Archivo guardado correctamente.';

        $this->reset('archivo');

        // ✅ emite el MISMO nombre que escucha el Blade (slug en minúsculas con _)
        $evento = 'archivo-guardado-' . Str::slug($this->wireId, '_');
        $this->dispatch($evento, nombre: $this->nombreArchivo, tamano: $this->tamanoArchivo);
    }

    public function cargarArchivoGuardado()
    {
        if (!$this->estudiante && $this->inscripcionId) {
            $this->estudiante = Inscripcion::find($this->inscripcionId);
        }

        $columna = $this->getColumna();
        $nombre = $columna ? ($this->estudiante?->$columna ?: null) : null;

        if ($nombre) {
            $ruta = $this->rutaGuardado . '/' . $nombre;

            if (Storage::exists($ruta)) {
                $this->archivoGuardadoUrl = Storage::url($ruta);
                $this->guardado = true;
                $this->nombreArchivo = $nombre;
                $this->tamanoArchivo = $this->formatoTamano(Storage::size($ruta));
            } else {
                $this->archivoGuardadoUrl = null;
                $this->guardado = false;
                $this->nombreArchivo = '';
                $this->tamanoArchivo = '';
            }
        } else {
            $this->archivoGuardadoUrl = null;
            $this->guardado = false;
            $this->nombreArchivo = '';
            $this->tamanoArchivo = '';
        }

        $this->mensaje = null;
    }

    public function eliminarArchivo()
    {
        if (!$this->estudiante || !$this->rutaGuardado) return;

        $columna = $this->getColumna();
        $nombre = $columna ? ($this->estudiante?->$columna ?: $this->generarNombrePersonalizado()) : $this->generarNombrePersonalizado();
        $ruta = $this->rutaGuardado . '/' . $nombre;

        if (Storage::exists($ruta)) {
            Storage::delete($ruta);
        }

        if ($columna) {
            $this->estudiante->$columna = null;
            $this->estudiante->save();
        }

        $this->archivoGuardadoUrl = null;
        $this->nombreArchivo = '';
        $this->tamanoArchivo = '';
        $this->guardado = false;
        $this->mensaje = null;

        $this->dispatch('swal', title: '¡Archivo eliminado correctamente!', icon: 'success', position: 'top');

        // ✅ mismo nombre que escucha el Blade
        $evento = 'archivo-eliminado-' . Str::slug($this->wireId, '_');
        $this->dispatch($evento);
    }

    /**
     * Columna de `inscripciones` a partir del wireId, normalizando el case.
     */
    protected function getColumna(): ?string
    {
        // mapa en minúsculas → nombre real de columna
        $map = [
            'curp_documento'      => 'CURP_documento',
            'acta_nacimiento'     => 'acta_nacimiento',
            'certificado_estudios'=> 'certificado_estudios',
            'comprobante_domicilio'=> 'comprobante_domicilio',
            'certificado_medico'  => 'certificado_medico',
            'ine'                  => 'ine',
        ];

        $id = Str::slug((string)$this->wireId, '_'); // p.ej. CURP_documento → curp_documento
        return $map[$id] ?? null;
    }

    public function generarNombrePersonalizado()
    {
        if (!$this->estudiante && $this->inscripcionId) {
            $this->estudiante = Inscripcion::find($this->inscripcionId);
        }

        if (!$this->estudiante) {
            return uniqid('archivo_', true) . '.pdf';
        }

        $matricula = preg_replace('/[^A-Za-z0-9]/', '', $this->estudiante->matricula ?? 'sinmatricula');
        $curp = preg_replace('/[^A-Za-z0-9]/', '', $this->estudiante->CURP ?? 'sincurp');
        $label = preg_replace('/[^A-Za-z0-9]/', '', $this->label ?? 'documento');

        return strtoupper("{$label}_{$matricula}_{$curp}.pdf");
    }

    public function formatoTamano($bytes)
    {
        $kb = $bytes / 1024;
        return number_format($kb, 1) . ' KB';
    }

    public function render()
    {
        return view('livewire.admin.documentacion.carga-documentos');
    }
}
