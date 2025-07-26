<?php

namespace App\Livewire\Admin\Estudiante;

use App\Models\Inscripcion;
use Livewire\Component;

class Estudiante extends Component
{

       public $query = '';
    public $alumnos = [];
    public $selectedIndex = 0;
    public $selectedAlumno = null;

    public $generacion_id;
    public $documento_expedicion;

    public $edad;
    public $fechaNacimiento;


    public function updatedQuery()
    {
        $this->buscarAlumnos();
    }

    public function buscarAlumnos()
    {
        if (strlen($this->query) > 0) {
            $this->alumnos = Inscripcion::with(['licenciatura', 'user', 'generacion', 'modalidad', 'cuatrimestre']) // <--- aquí!
            ->where('nombre', 'like', '%' . $this->query . '%')
            ->orWhere('apellido_paterno', 'like', '%' . $this->query . '%')
            ->orWhere('apellido_materno', 'like', '%' . $this->query . '%')
            ->orWhere('curp', 'like', '%' . $this->query . '%')
            ->orWhere('matricula', 'like', '%' . $this->query . '%')
            ->get()
            ->toArray();

        } else {
            $this->alumnos = [];
        }
        $this->selectedIndex = 0;
    }

    public function selectAlumno($index)
    {
        if (isset($this->alumnos[$index])) {
            $this->selectedAlumno = $this->alumnos[$index];
            $this->query = $this->selectedAlumno['nombre'] . ' ' . $this->selectedAlumno['apellido_paterno'] . ' ' . $this->selectedAlumno['apellido_materno'] . ' | ' . $this->selectedAlumno['CURP'] . ' | ' . $this->selectedAlumno['matricula'];
            $this->alumnos = [];

            // Calcular edad
            $this->calcularEdad($this->selectedAlumno['CURP']);
        } else {
            $this->dispatch('swal',[
                'title' => 'Alumno no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);
        }
    }

    public function selectIndexUp()
    {
        if ($this->alumnos) {
            $this->selectedIndex = ($this->selectedIndex - 1 + count($this->alumnos)) % count($this->alumnos);
        }
    }

    public function selectIndexDown()
    {
        if ($this->alumnos) {
            $this->selectedIndex = ($this->selectedIndex + 1) % count($this->alumnos);
        }
    }

    // CALCULAR EDAD DEL ALUMNO MEDIANTE EL CURP
    public function calcularEdad($curp)
    {
          $fecha = substr($curp, 4, 6); // AAMMDD

                    $anio = substr($fecha, 0, 2);
                    $mes = substr($fecha, 2, 2);
                    $dia = substr($fecha, 4, 2);

                    $anio_completo = intval($anio) < 30 ? "20$anio" : "19$anio";

                    $fecha_nacimiento = "$anio_completo-$mes-$dia";
                    $this->fechaNacimiento = $fecha_nacimiento;



                    // Calcular edad
                    try {
                        $nacimiento = new \DateTime($fecha_nacimiento);
                        $hoy = new \DateTime();
                        $edad = $hoy->diff($nacimiento)->y;
                        $this->edad = $edad;
                    } catch (\Exception $e) {
                        $this->edad = null; // Si hay error en fecha
                    }

    }

    public function render()
    {
        return view('livewire.admin.estudiante.estudiante');
    }
}
