<?php

namespace App\Livewire\Admin\Estudiante;

use App\Models\Inscripcion;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class Estudiante extends Component
{
    public $query = '';

    public array $alumnos = [];

    public int $selectedIndex = 0;

    public $selectedAlumno = null;

    public $generacion_id;

    public $documento_expedicion;

    public $edad;

    public $fechaNacimiento;

    public function isEgresado($alumno): bool
    {
        return isset($alumno['generacion']['activa']) && $alumno['generacion']['activa'] === 'false';
    }

    public function isBaja($alumno): bool
    {
        return isset($alumno['status']) && $alumno['status'] === 'false';
    }

    /**
     * Cada vez que se escribe en el buscador, se buscan alumnos.
     */
    public function updatedQuery(): void
    {
        $this->buscarAlumnos();
    }

    public function buscarAlumnos(): void
    {
        $texto = trim($this->query);

        if (strlen($texto) < 2) {
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
            ->where(function ($consulta) use ($texto) {
                $consulta->where('nombre', 'like', '%' . $texto . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $texto . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $texto . '%')
                    ->orWhere('CURP', 'like', '%' . $texto . '%')
                    ->orWhere('matricula', 'like', '%' . $texto . '%')
                    ->orWhere('folio', 'like', '%' . $texto . '%');
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->limit(10)
            ->get()
            ->toArray();

        $this->selectedIndex = 0;
    }

    public function selectAlumno($index): void
    {
        if (empty($this->alumnos) || ! isset($this->alumnos[$index])) {
            $this->dispatch('swal', [
                'title' => 'Alumno no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);

            return;
        }

        $alumnoSeleccionado = $this->alumnos[$index];

        $this->cargarAlumno($alumnoSeleccionado['id']);
    }

    public function cargarAlumno($alumnoId): void
    {
        if (empty($alumnoId)) {
            $this->limpiarAlumno();

            return;
        }

        $alumno = Inscripcion::query()
            ->with([
                'licenciatura',
                'user',
                'generacion',
                'modalidad',
                'cuatrimestre',
                'ciudadNacimiento',
                'estadoNacimiento',
                'ciudad',
                'estado',
                'documentosIdentidadActuales.usuario',
            ])
            ->find($alumnoId);

        if (! $alumno) {
            $this->limpiarAlumno();

            $this->dispatch('swal', [
                'title' => 'Alumno no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);

            return;
        }

        $nombreCompleto = trim(
            ($alumno->apellido_paterno ?? '') . ' ' .
                ($alumno->apellido_materno ?? '') . ' ' .
                ($alumno->nombre ?? '')
        );

        $datosAlumno = $alumno->toArray();
        $documentosFicha = [];
        $disk = (string) config('documentos_identidad.disk', 'local');
        $actuales = $alumno->documentosIdentidadActuales->keyBy('tipo');

        foreach (config('documentos_identidad.types', []) as $tipo => $config) {
            $documento = $actuales->get($tipo);
            $existe = $documento && Storage::disk($disk)->exists($documento->ruta);

            $documentosFicha[$tipo] = [
                'label' => $config['label'],
                'obligatorio' => (bool) $config['required'],
                'entregado' => (bool) $existe,
                'url' => $existe ? route('admin.documentos-identidad.ver', $documento) : null,
                'fecha' => $existe ? optional($documento->created_at)->format('d/m/Y H:i') : null,
            ];
        }

        $datosAlumno['documentos_identidad_ficha'] = $documentosFicha;
        $datosAlumno['documentos_identidad_porcentaje'] = count($documentosFicha) > 0
            ? (int) round((collect($documentosFicha)->where('entregado', true)->count() / count($documentosFicha)) * 100)
            : 0;

        $this->query = $nombreCompleto;
        $this->selectedAlumno = $datosAlumno;
        $this->alumnos = [];
        $this->selectedIndex = 0;

        $this->calcularEdad($this->selectedAlumno['CURP'] ?? '');
    }

    public function selectIndexUp(): void
    {
        if (! empty($this->alumnos)) {
            $this->selectedIndex = ($this->selectedIndex - 1 + count($this->alumnos)) % count($this->alumnos);
        }
    }

    public function selectIndexDown(): void
    {
        if (! empty($this->alumnos)) {
            $this->selectedIndex = ($this->selectedIndex + 1) % count($this->alumnos);
        }
    }

    public function calcularEdad($curp): void
    {
        $this->edad = null;
        $this->fechaNacimiento = null;

        if (empty($curp) || strlen($curp) < 10) {
            return;
        }

        $fecha = substr($curp, 4, 6);
        $anio = substr($fecha, 0, 2);
        $mes = substr($fecha, 2, 2);
        $dia = substr($fecha, 4, 2);

        $anioCompleto = intval($anio) < 30 ? "20{$anio}" : "19{$anio}";
        $fechaNacimiento = "{$anioCompleto}-{$mes}-{$dia}";

        $this->fechaNacimiento = $fechaNacimiento;

        try {
            $nacimiento = new \DateTime($fechaNacimiento);
            $hoy = new \DateTime();

            $this->edad = $hoy->diff($nacimiento)->y;
        } catch (\Exception $e) {
            $this->edad = null;
        }
    }

    public function limpiarAlumno(): void
    {
        $this->query = '';
        $this->alumnos = [];
        $this->selectedIndex = 0;
        $this->selectedAlumno = null;
        $this->edad = null;
        $this->fechaNacimiento = null;
    }

    #[On('refreshMatricula')]
    public function refreshAlumno(): void
    {
        if (! $this->selectedAlumno || ! isset($this->selectedAlumno['id'])) {
            return;
        }

        $this->cargarAlumno($this->selectedAlumno['id']);
    }

    public function render()
    {
        return view('livewire.admin.estudiante.estudiante');
    }
}
