<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Services\DocumentosIdentidad\DocumentoIdentidadService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

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

    public array $documentosResumen = [];

    public array $pendientes = [];

    public int $documentosEntregados = 0;

    public int $documentosTotales = 0;

    public int $obligatoriosEntregados = 0;

    public int $obligatoriosTotales = 0;

    public int $porcentaje = 0;

    public bool $tieneDocumentos = false;

    public function mount(): void
    {
        $this->licenciaturas = Licenciatura::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->toArray();

        $this->cargarGeneraciones();
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
            ->when($this->estado === 'activos', fn (Builder $q) => $q->where('status', 'true')->where('egresado', 'false'))
            ->when($this->estado === 'egresados', fn (Builder $q) => $q->where('egresado', 'true'))
            ->when($this->estado === 'bajas', fn (Builder $q) => $q->where('status', 'false'))
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

    protected function actualizarResumen(): void
    {
        $this->limpiarResumen();

        if (! $this->selectedAlumno) {
            return;
        }

        $service = app(DocumentoIdentidadService::class);
        $disk = $service->disk();

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
    }

    public function render()
    {
        return view('livewire.admin.documentacion.identidad');
    }
}
