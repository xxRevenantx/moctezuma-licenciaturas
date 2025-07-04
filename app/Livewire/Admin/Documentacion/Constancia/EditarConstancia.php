<?php

namespace App\Livewire\Admin\Documentacion\Constancia;

use App\Models\Constancia;
use App\Models\Inscripcion;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarConstancia extends Component
{
    public $query = '';
    public $alumnos = [];
    public $selectedIndex = 0;
    public $selectedAlumno = null;
    public $open = false;
    public $constancia_id;

    public $no_constancia;
    public $alumno;
    public $alumno_id;
    public $tipo_constancia;
    public $fecha_expedicion  ;


        // Método para abrir el modal con datos
    #[On('abrirConstancia')]
    public function abrirModal($id)
    {
        $constancia = Constancia::findOrFail($id);
         $this->constancia_id = $constancia->id;
         $this->no_constancia = $constancia->no_constancia;
         $this->alumno_id = $constancia->alumno_id;
         $this->alumno = $constancia->alumno;
         $this->tipo_constancia = $constancia->tipo_constancia;
         $this->fecha_expedicion   = $constancia->fecha_expedicion;

        $this->open = true;

    }

    public function actualizarConstancia()
{
    $this->validate(
        [
            'tipo_constancia' => 'required',
            'fecha_expedicion' => 'required|date',
        ],
        [
            'tipo_constancia.required' => 'El tipo de constancia es obligatorio.',
            'fecha_expedicion.required' => 'La fecha de expedición es obligatoria.',
            'fecha_expedicion.date' => 'La fecha de expedición debe ser una fecha válida.',
            'alumno_id.required' => 'El alumno es obligatorio.',
            'alumno_id.exists' => 'El alumno seleccionado no existe.',
        ]
    );



    $constancia = Constancia::findOrFail($this->constancia_id);

    $constancia->update([
        'tipo_constancia' => $this->tipo_constancia,
        'fecha_expedicion' => $this->fecha_expedicion,
        'no_constancia' => $this->no_constancia,
        'alumno_id' => $this->selectedAlumno['id'] ?? $this->alumno_id,
    ]);

    $this->dispatch('swal', [
        'title' => '¡Constancia actualizada correctamente!',
        'icon' => 'success',
        'position' => 'top-end',
    ]);

    $this->dispatch('refreshConstancias');
    $this->cerrarModal();
}





        public function cerrarModal()
    {
        $this->reset(['open', 'constancia_id', 'query', 'alumnos', 'selectedIndex', 'selectedAlumno']);
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
        return view('livewire.admin.documentacion.constancia.editar-constancia');
    }
}
