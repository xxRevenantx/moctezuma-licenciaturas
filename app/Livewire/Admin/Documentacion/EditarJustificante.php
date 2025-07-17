<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Inscripcion;
use App\Models\Justificante;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarJustificante extends Component
{

    public $query = '';
    public $alumnos = [];
    public $selectedIndex = 0;
    public $selectedAlumno = null;


    public $justificanteId;
    public $alumno_id;

    public $justificacion;
    public $fecha_expedicion;
    public $fechas_justificacion;
    public $open = false;


    #[On('abrirJustificante')]
    public function abrirModal($id)
    {
        $justificante = Justificante::findOrFail($id);


        $this->justificanteId = $justificante->id;
        $this->alumno_id = Inscripcion::findOrFail($justificante->alumno_id);
        $this->justificacion = $justificante->justificacion;
        $this->fecha_expedicion = $justificante->fecha_expedicion;
        $this->fechas_justificacion = $justificante->fechas_justificacion;

        $this->open = true;

    }

    public function actualizarJustificante()
    {
        $this->validate([
            'justificacion' => 'required|string|max:255',
            'fecha_expedicion' => 'required|date',
            'fechas_justificacion' => 'required|string',
        ], [
            'justificacion.required' => 'El campo Justificación es obligatorio.',
            'fecha_expedicion.required' => 'El campo Fecha de Expedición es obligatorio.',
            'fechas_justificacion.required' => 'El campo Fechas es obligatorio.',
        ]);



        $asignacion = Justificante::findOrFail($this->justificanteId);
        $asignacion->update([
           'alumno_id' => $this->selectedAlumno['id'] ?? $this->alumno_id->id,
            'justificacion' => $this->justificacion,
            'fecha_expedicion' => $this->fecha_expedicion,
            'fechas_justificacion' => $this->fechas_justificacion,
        ]);


        $this->dispatch('refreshJustificantes');

        $this->dispatch('swal', [
            'title' => 'Justificante actualizado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
        $this->cerrarModal();
    }



    public function cerrarModal()
    {
        $this->reset([
            'justificanteId',
            'justificacion',
            'fecha_expedicion',
            'fechas_justificacion',
            'open',
            'query',
            'alumnos',
            'selectedIndex',
            'selectedAlumno',
        ]);
        $this->resetValidation();
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
        return view('livewire.admin.documentacion.editar-justificante');
    }
}
