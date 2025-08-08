<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Inscripcion;
use Livewire\Component;
use Livewire\Attributes\On;

class Identidad extends Component
{
    use \Livewire\WithFileUploads;

    public $query = '';
    public $alumnos = [];
    public $selectedIndex = 0;
    public $selectedAlumno = null;

    public $tieneDocumentos = false;

    public function updatedQuery()
    {
        $this->buscarAlumnos();
    }

    public function buscarAlumnos()
    {
        if (strlen($this->query) > 0) {
            $this->alumnos = Inscripcion::with('licenciatura')
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
            $this->query = $this->selectedAlumno['nombre'] . ' ' .
                           $this->selectedAlumno['apellido_paterno'] . ' ' .
                           $this->selectedAlumno['apellido_materno'] . ' - ' .
                           $this->selectedAlumno['matricula'] . ' - ' .
                           $this->selectedAlumno['CURP'];

            $this->verificarDocumentos();

            $this->dispatch('alumnoSeleccionado', $this->selectedAlumno['id']);
            $this->alumnos = [];
        } else {
            $this->dispatch('swal', [
                'title' => 'Alumno no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);
        }
    }

    #[On('archivo-guardado')]
    #[On('archivo-eliminado')]
    public function verificarDocumentos()
    {
        if (!$this->selectedAlumno) {
            $this->tieneDocumentos = false;
            return;
        }

        $inscripcion = Inscripcion::find($this->selectedAlumno['id']);

        // dd($inscripcion);
        $this->tieneDocumentos = collect([
            $inscripcion->CURP_documento,
            $inscripcion->acta_nacimiento,
            $inscripcion->certificado_estudios,
            $inscripcion->comprobante_domicilio,
            $inscripcion->certificado_medico,
            $inscripcion->ine
        ])->filter()->isNotEmpty();



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
        return view('livewire.admin.documentacion.identidad');
    }
}
