<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Inscripcion;
use Livewire\Attributes\On;
use Livewire\Component;

class Justificantes extends Component
{

    public $query = '';
    public $alumnos = [];
    public $selectedIndex = 0;
    public $selectedAlumno = null;

    public $justificaciones;
    public $fechas;
    public $fecha_expedicion;


      public function updatedQuery()
    {
        $this->buscarAlumnos();
    }

    public function buscarAlumnos()
    {
        if (strlen($this->query) > 0) {
            $this->alumnos = Inscripcion::with('licenciatura') // <--- aquí!
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
            $this->query = $this->selectedAlumno['matricula'];
            $this->alumnos = [];
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


    public function crearJustificante()
    {
        $this->validate([
            'selectedAlumno' => 'required',
            'selectedAlumno.id' => 'exists:inscripciones,id',
            'fechas' => 'required|string',
            'justificaciones' => 'required|string',
            'fecha_expedicion' => 'required|date',
        ]);



        if ($this->selectedAlumno) {
            $justificante = new \App\Models\Justificante();
            $justificante->alumno_id = $this->selectedAlumno['id'];
            $justificante->fechas_justificacion = $this->fechas;
            $justificante->justificacion = $this->justificaciones;
            $justificante->fecha_expedicion = $this->fecha_expedicion;
            $justificante->save();

            $this->dispatch('swal', [
                'title' => '¡Justificante creado exitosamente!',
                'icon' => 'success',
                'position' => 'top',
            ]);

            // Reset form fields
            $this->reset(['fechas', 'justificaciones', 'fecha_expedicion']);
            $this->selectedAlumno = null;
            $this->query = '';
            $this->alumnos = [];
            $this->selectedIndex = 0;

        } else {
            $this->dispatch('swal', [
                'title' => 'Selecciona un alumno primero',
                'icon' => 'warning',
                'position' => 'top',
            ]);
        }
    }

    // ELIMINAR JUSTIFICANTE
    public function eliminarJustificante($id)
    {
        $justificante = \App\Models\Justificante::find($id);
        if ($justificante) {
            $justificante->delete();
            $this->dispatch('swal', [
                'title' => '¡Justificante eliminado exitosamente!',
                'icon' => 'success',
                'position' => 'top',
            ]);
        } else {
            $this->dispatch('swal', [
                'title' => 'Justificante no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);
        }
    }


    #[On('refreshJustificantes')]
    public function render()
    {
        $justificantes = \App\Models\Justificante::with('alumno')->get();
        return view('livewire.admin.documentacion.justificantes', compact('justificantes'));
    }
}
