<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Generacion;
use App\Models\Inscripcion;
use Livewire\Component;

class ActaExamen extends Component
{
    public string $fecha = '';
    public array $sinodos = [''];

    public $query = '';

    public $selectedAlumno = null;

    protected $rules = [
        'fecha' => 'required|date',
        'sinodos' => 'required|array|min:1',
        'sinodos.*' => 'required|string|max:255',
    ];

    public function updatedQuery($value)
    {
        // Si limpian el select
        if (empty($value)) {
            $this->selectedAlumno = null;
            $this->edad = null;
            $this->fechaNacimiento = null;
            return;
        }

        // Buscar alumno con todas sus relaciones
        $alumno = Inscripcion::with([
            'licenciatura',
            'user',
            'generacion',
            'modalidad',
            'cuatrimestre',
            'ciudadNacimiento',
            'estadoNacimiento',
            'ciudad',
            'estado',
        ])->find($value);

        if ($alumno) {
            $this->selectedAlumno = $alumno->toArray();
        } else {
            $this->selectedAlumno = null;

            $this->dispatch('swal', [
                'title' => 'Alumno no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);
        }
    }

    public function addSinodo(): void
    {
        $this->sinodos[] = '';
    }

    public function removeSinodo(int $index): void
    {
        unset($this->sinodos[$index]);
        $this->sinodos = array_values($this->sinodos);
    }

    public function moveSinodoUp(int $index): void
    {
        if ($index <= 0) {
            return;
        }

        // Intercambia el actual con el anterior
        [$this->sinodos[$index - 1], $this->sinodos[$index]] =
            [$this->sinodos[$index], $this->sinodos[$index - 1]];
    }

    public function moveSinodoDown(int $index): void
    {
        if ($index >= count($this->sinodos) - 1) {
            return;
        }

        // Intercambia el actual con el siguiente
        [$this->sinodos[$index + 1], $this->sinodos[$index]] =
            [$this->sinodos[$index], $this->sinodos[$index + 1]];
    }

    protected $messages = [
        'fecha.required' => 'La fecha es obligatoria.',
        'fecha.date' => 'La fecha debe ser una fecha válida.',
        'sinodos.required' => 'Al menos un sinodo es obligatorio.',
        'sinodos.array' => 'Los sinodos deben ser un array.',
        'sinodos.min' => 'Al menos un sinodo es obligatorio.',
        'sinodos.*.required' => 'El sinodo es obligatorio.',
        'sinodos.*.string' => 'El sinodo debe ser una cadena de texto.',
        'sinodos.*.max' => 'El sinodo debe tener un máximo de 255 caracteres.',
    ];

    public function guardarActaExamen()
    {
        $this->validate();

        dd($this->fecha, $this->sinodos);
        // Guardado...
    }

    public function render()
    {
        // Opciones del select (no hace búsqueda, solo lista; la búsqueda la hace el componente Blade)
        $alumnos = Inscripcion::with([
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
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')

            ->get()
            ->toArray();

        $generaciones = Generacion::all();
        return view('livewire.admin.documentacion.acta-examen', compact('alumnos', 'generaciones'));
    }
}
