<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\AsignarGeneracion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Generacion;
use Livewire\Attributes\On;
use Livewire\Component;

class Identidad extends Component
{
    use \Livewire\WithFileUploads;

    /** Buscador de alumnos */
    public string $query = '';
    public array $alumnos = [];
    public int $selectedIndex = 0;
    public ?array $selectedAlumno = null;
    public bool $tieneDocumentos = false;

    /** Filtros dependientes */
    public ?int $selectedLicenciatura = null;
    public ?int $selectedGeneracion  = null;

    /** Catálogos para los selects */
    public array $licenciaturas;
    public array $generaciones;

    public function mount(): void
    {
        // Solo columnas necesarias para el select
        $this->licenciaturas = Licenciatura::orderBy('nombre')
            ->get(['id','nombre'])
            ->toArray();

        // Arranca sin generaciones (hasta elegir licenciatura)
        $this->generaciones = [];
    }
public function updatedSelectedLicenciatura($licenciaturaId): void
{
    $this->selectedGeneracion = null;

    if ($licenciaturaId) {
        // Trae asignaciones con la relación "generacion" y aplana a un arreglo simple {id, generacion}
        $this->generaciones = \App\Models\AsignarGeneracion::with(['generacion:id,generacion'])
            ->where('licenciatura_id', $licenciaturaId)
            ->get(['id', 'generacion_id']) // necesitamos generacion_id para mapear
            ->filter(fn ($ag) => $ag->generacion) // evita nulos por integridad
            ->map(fn ($ag) => [
                'id'         => $ag->generacion->id,
                'generacion' => $ag->generacion->generacion,
            ])
            ->unique('id')   // evita duplicados si hay varias asignaciones a la misma generación
            ->values()
            ->toArray();
    } else {
        $this->generaciones = [];
    }
}


    public function updatedQuery(): void
    {
        $this->buscarAlumnos();
    }

    public function buscarAlumnos(): void
    {
        if (strlen($this->query) > 0) {
            $this->alumnos = Inscripcion::with('licenciatura')
                ->where(function ($q) {
                    $q->where('nombre', 'like', "%{$this->query}%")
                      ->orWhere('apellido_paterno', 'like', "%{$this->query}%")
                      ->orWhere('apellido_materno', 'like', "%{$this->query}%")
                      ->orWhere('curp', 'like', "%{$this->query}%")
                      ->orWhere('matricula', 'like', "%{$this->query}%");
                })
                // Si quieres que el buscador respete filtros, descomenta:
                // ->when($this->selectedLicenciatura, fn($q) => $q->where('licenciatura_id', $this->selectedLicenciatura))
                // ->when($this->selectedGeneracion,  fn($q) => $q->where('generacion_id',   $this->selectedGeneracion))
                ->get()
                ->toArray();
        } else {
            $this->alumnos = [];
        }
        $this->selectedIndex = 0;
    }

    public function selectAlumno($index): void
    {
        if (isset($this->alumnos[$index])) {
            $this->selectedAlumno = $this->alumnos[$index];
            $this->query = $this->selectedAlumno['nombre'].' '.
                           $this->selectedAlumno['apellido_paterno'].' '.
                           $this->selectedAlumno['apellido_materno'].' - '.
                           $this->selectedAlumno['matricula'].' - '.
                           ($this->selectedAlumno['curp'] ?? ''); // usa 'curp' en minúsculas

            $this->verificarDocumentos();
            $this->dispatch('alumnoSeleccionado', $this->selectedAlumno['id']);
            $this->alumnos = [];
        } else {
            $this->dispatch('swal', [
                'title'    => 'Alumno no encontrado',
                'icon'     => 'error',
                'position' => 'top',
            ]);
        }
    }

    #[On('archivo-guardado')]
    #[On('archivo-eliminado')]
    public function verificarDocumentos(): void
    {
        if (!$this->selectedAlumno) {
            $this->tieneDocumentos = false;
            return;
        }

        $inscripcion = Inscripcion::find($this->selectedAlumno['id']);

        $this->tieneDocumentos = collect([
            $inscripcion->CURP_documento,
            $inscripcion->acta_nacimiento,
            $inscripcion->certificado_estudios,
            $inscripcion->comprobante_domicilio,
            $inscripcion->certificado_medico,
            $inscripcion->ine,
        ])->filter()->isNotEmpty();
    }

    public function selectIndexUp(): void
    {
        if ($this->alumnos) {
            $this->selectedIndex = ($this->selectedIndex - 1 + count($this->alumnos)) % count($this->alumnos);
        }
    }

    public function selectIndexDown(): void
    {
        if ($this->alumnos) {
            $this->selectedIndex = ($this->selectedIndex + 1) % count($this->alumnos);
        }
    }

    public function render()
    {
        // Ya no consultes aquí; usa las propiedades cargadas en mount/updated*
        return view('livewire.admin.documentacion.identidad', [
            'licenciaturas' => $this->licenciaturas,
            'generaciones'  => $this->generaciones,
        ]);
    }
}
