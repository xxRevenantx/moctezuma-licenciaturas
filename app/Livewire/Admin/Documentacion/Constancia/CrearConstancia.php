<?php

namespace App\Livewire\Admin\Documentacion\Constancia;

use App\Models\Constancia;
use App\Models\Inscripcion;
use Livewire\Attributes\On;
use Livewire\Component;

class CrearConstancia extends Component
{
    public $query = '';

    public array $alumnos = [];

    public int $selectedIndex = 0;

    public $selectedAlumno = null;

    public $generacion_id;

    public $tipo_constancia = '';

    public $fecha_expedicion;

    public $no_constancia;

    public function mount(): void
    {
        $this->actualizarNumeroConstancia();
    }

    #[On('eliminarConstancia')]
    public function actualizarNumeroConstancia(): void
    {
        $ultimoNumero = Constancia::query()->max('no_constancia');

        $this->no_constancia = $ultimoNumero ? ((int) $ultimoNumero + 1) : 1;
    }

    public function guardarConstancia(): void
    {
        $this->validate([
            'tipo_constancia' => ['required'],
            'fecha_expedicion' => ['required', 'date'],
            'no_constancia' => ['required'],
        ], [
            'tipo_constancia.required' => 'Selecciona el tipo de constancia.',
            'fecha_expedicion.required' => 'Selecciona la fecha de expedición.',
            'fecha_expedicion.date' => 'La fecha de expedición no es válida.',
            'no_constancia.required' => 'El número de constancia es obligatorio.',
        ]);

        if (! $this->selectedAlumno || ! isset($this->selectedAlumno['id'])) {
            $this->dispatch('swal', [
                'title' => 'Selecciona un alumno primero',
                'icon' => 'error',
                'position' => 'top',
            ]);

            return;
        }

        $alumno = Inscripcion::query()->find($this->selectedAlumno['id']);

        if (! $alumno) {
            $this->limpiarAlumno();

            $this->dispatch('swal', [
                'title' => 'Alumno no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);

            return;
        }

        $alumno->constancias()->create([
            'alumno_id' => $alumno->id,
            'tipo_constancia' => $this->tipo_constancia,
            'no_constancia' => $this->no_constancia,
            'fecha_expedicion' => $this->fecha_expedicion,
        ]);

        $this->dispatch('swal', [
            'title' => 'Constancia creada exitosamente',
            'icon' => 'success',
            'position' => 'top',
        ]);

        $this->reset([
            'tipo_constancia',
            'fecha_expedicion',
        ]);

        $this->limpiarAlumno();
        $this->actualizarNumeroConstancia();

        $this->dispatch('refreshConstancias');
    }

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
        if (! isset($this->alumnos[$index])) {
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

        $this->query = $nombreCompleto;
        $this->selectedAlumno = $alumno->toArray();
        $this->alumnos = [];
        $this->selectedIndex = 0;
    }

    public function limpiarAlumno(): void
    {
        $this->query = '';
        $this->alumnos = [];
        $this->selectedIndex = 0;
        $this->selectedAlumno = null;
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

    public function render()
    {
        return view('livewire.admin.documentacion.constancia.crear-constancia');
    }
}
