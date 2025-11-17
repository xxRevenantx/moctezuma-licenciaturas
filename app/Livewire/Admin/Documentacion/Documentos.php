<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Generacion;
use App\Models\Inscripcion;
use Livewire\Component;

class Documentos extends Component
{
    public $query = '';
    public $selectedIndex = 0;
    public $selectedAlumno = null;

    public $generacion_id;
    public $documento_expedicion;


    public function updatedQuery($value)
    {
        // Si limpian el select
        if (empty($value)) {
            $this->selectedAlumno   = null;
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
                'title'    => 'Alumno no encontrado',
                'icon'     => 'error',
                'position' => 'top',
            ]);
        }
    }



    // expedicion de documentos de escolaridad
    public function expedirDocumento()
    {
        $this->validate([
            'generacion_id' => 'required',
            'documento_expedicion' => 'required',
        ]);

    return redirect()->route('admin.pdf.documentacion.documento_expedicion', [
            'generacion' => $this->generacion_id,
            'documento' => $this->documento_expedicion,
        ]);
    }




    public function render()
    {
        $generaciones = Generacion::all();
         $alumnos = Inscripcion::with([
                'licenciatura', 'user', 'generacion', 'modalidad',
                'cuatrimestre', 'ciudadNacimiento', 'estadoNacimiento',
                'ciudad', 'estado',
            ])
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')

            ->get();


        return view('livewire.admin.documentacion.documentos', [
            'generaciones' => $generaciones,
            'licenciaturas' => \App\Models\Licenciatura::all(),
            'alumnos' => $alumnos,

        ]);
    }
}
