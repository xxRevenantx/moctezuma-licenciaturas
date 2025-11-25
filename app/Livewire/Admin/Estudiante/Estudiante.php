<?php

namespace App\Livewire\Admin\Estudiante;

use App\Models\Inscripcion;
use Livewire\Component;
use Livewire\Attributes\On;

class Estudiante extends Component
{
    public $query = '';          // AHORA guarda el ID del alumno seleccionado
    public $selectedIndex = 0;   // ya no se usa, lo puedes borrar si quieres
    public $selectedAlumno = null;

    public $generacion_id;
    public $documento_expedicion;

    public $edad;
    public $fechaNacimiento;

    public function isEgresado($alumno)
    {
        return isset($alumno['generacion']['activa']) && $alumno['generacion']['activa'] === "false";
    }

    public function isBaja($alumno)
    {
        return isset($alumno['status']) && $alumno['status'] === "false";
    }

    /**
     * Cuando cambia el valor del select (query = id del alumno),
     * cargamos los datos completos y calculamos la edad.
     */
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
            $this->calcularEdad($this->selectedAlumno['CURP'] ?? '');
        } else {
            $this->selectedAlumno = null;

            $this->dispatch('swal', [
                'title' => 'Alumno no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);
        }
    }

    public function calcularEdad($curp)
    {
        $fecha = substr($curp, 4, 6); // AAMMDD
        $anio = substr($fecha, 0, 2);
        $mes = substr($fecha, 2, 2);
        $dia = substr($fecha, 4, 2);

        $anio_completo = intval($anio) < 30 ? "20$anio" : "19$anio";
        $fecha_nacimiento = "$anio_completo-$mes-$dia";

        $this->fechaNacimiento = $fecha_nacimiento;

        try {
            $nacimiento = new \DateTime($fecha_nacimiento);
            $hoy = new \DateTime();
            $this->edad = $hoy->diff($nacimiento)->y;
        } catch (\Exception $e) {
            $this->edad = null;
        }
    }

    public function limpiarAlumno()
    {
        $this->query = '';
        $this->selectedAlumno = null;
        $this->edad = null;
        $this->fechaNacimiento = null;
    }

    #[On('refreshMatricula')]
    public function refreshAlumno()
    {
        if ($this->selectedAlumno && isset($this->selectedAlumno['id'])) {
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
            ])->find($this->selectedAlumno['id']);

            if ($alumno) {
                $this->selectedAlumno = $alumno->toArray();
                $this->calcularEdad($this->selectedAlumno['CURP'] ?? '');
            }
        }
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

        return view('livewire.admin.estudiante.estudiante', compact('alumnos'));
    }
}
