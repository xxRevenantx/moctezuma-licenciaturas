<?php

namespace App\Livewire\Admin\Documentacion\Constancia;

use App\Models\Constancia;
use App\Models\Inscripcion;
use Livewire\Component;

class CrearConstancia extends Component
{
     public $query = '';
    public $alumnos = [];
    public $selectedIndex = 0;
    public $selectedAlumno = null;

    public $generacion_id;
    public $documento_expedicion;

    public $tipo_constancia = '';
    public $fecha;
    public $no_constancia;



    public function mount()
    {
        $this->no_constancia = Constancia::max('no_constancia') + 1;

    }

    public function guardarConstancia(){
        $this->validate([
            'tipo_constancia' => 'required',
            'fecha' => 'required|date',
        ]);

        if ($this->selectedAlumno) {
            Inscripcion::find($this->selectedAlumno['id'])->constancias()->create([
                'alumno_id' => $this->selectedAlumno['id'],
                'tipo_constancia' => $this->tipo_constancia,
                'no_constancia' => $this->selectedAlumno['matricula'],
                'fecha_expedicion' => $this->fecha,
            ]);

            $this->dispatch('swal', [
                'title' => 'Constancia creada exitosamente',
                'icon' => 'success',
                'position' => 'top',
            ]);

            $this->reset(['tipo_constancia', 'fecha']);
            $this->selectedAlumno = null;
            $this->query = '';
            $this->alumnos = [];
            $this->selectedIndex = 0;
             $this->no_constancia = Constancia::max('no_constancia') + 1;


        } else {
            $this->dispatch('swal', [
                'title' => 'Selecciona un alumno primero',
                'icon' => 'error',
                'position' => 'top',
            ]);
        }


    }



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





    public function render()
    {
        return view('livewire.admin.documentacion.constancia.crear-constancia');
    }
}
