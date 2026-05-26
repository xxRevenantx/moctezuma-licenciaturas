<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use Livewire\Component;

class Documentos extends Component
{
    public string $query = '';

    public array $alumnos = [];

    public int $selectedIndex = 0;

    public $alumno_id = null;

    public $selectedAlumno = null;

    public $generacion_id = null;

    public $documento_expedicion = null;

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
                'generacion:id,generacion',
                'modalidad:id,nombre',
                'cuatrimestre:id,nombre_cuatrimestre',
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
                'text' => 'No fue posible cargar la información del alumno seleccionado.',
                'icon' => 'error',
                'position' => 'top-end',
            ]);

            return;
        }

        $alumnoSeleccionado = $this->alumnos[$index];

        $this->alumno_id = $alumnoSeleccionado['id'];

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
            ->find($this->alumno_id);

        if (! $alumno) {
            $this->limpiarAlumno();

            $this->dispatch('swal', [
                'title' => 'Alumno no encontrado',
                'text' => 'No fue posible cargar la información completa del alumno.',
                'icon' => 'error',
                'position' => 'top-end',
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
        $this->alumno_id = null;
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

    public function expedirDocumento()
    {
        $this->validate([
            'generacion_id' => ['required'],
            'documento_expedicion' => ['required'],
        ], [
            'generacion_id.required' => 'Selecciona una generación.',
            'documento_expedicion.required' => 'Selecciona el documento que deseas expedir.',
        ]);

        return redirect()->route('admin.pdf.documentacion.documento_expedicion', [
            'generacion' => $this->generacion_id,
            'documento' => $this->documento_expedicion,
        ]);
    }

    public function render()
    {
        $generaciones = Generacion::query()
            ->orderByDesc('id')
            ->get();

        $licenciaturas = Licenciatura::query()
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.documentacion.documentos', [
            'generaciones' => $generaciones,
            'licenciaturas' => $licenciaturas,
        ]);
    }
}
