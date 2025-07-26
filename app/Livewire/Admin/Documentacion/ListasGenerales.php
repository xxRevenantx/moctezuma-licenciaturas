<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Inscripcion;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ListasGenerales extends Component
{

    public $licenciatura_id = null;
    public $alumnos;
    public $licenciatura_nombre;
    public $search = '';
    public $filtrar_foraneo = null;

    use WithPagination;



 public function consultarListas()
{
    $this->validate([
        'licenciatura_id' => 'required|exists:licenciaturas,id',
    ], [
        'licenciatura_id.required' => 'Debes seleccionar una licenciatura.',
        'licenciatura_id.exists' => 'La licenciatura seleccionada no es válida.',
    ]);

    $this->alumnos = Inscripcion::where('licenciatura_id', $this->licenciatura_id)
        ->where('status', 'true')
        ->whereHas('generacion', function ($query) {
            $query->where('activa', 'true');
        })
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                    ->orWhere('matricula', 'like', '%' . $this->search . '%')
                    ->orWhereRaw("CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) LIKE ?", ['%' . $this->search . '%']);

            });
        })
        ->with(['licenciatura', 'generacion', 'modalidad', 'cuatrimestre'])
        ->orderBy('apellido_paterno')
        ->orderBy('apellido_materno')
        ->orderBy('nombre')
        ->get();

    $this->licenciatura_nombre = \App\Models\Licenciatura::find($this->licenciatura_id);
}



    public function updatedSearch()
    {
        if ($this->licenciatura_id) {
            $this->consultarListas();
        }
    }


    public function render()
    {
        $licenciaturas = \App\Models\Licenciatura::all();
        return view('livewire.admin.documentacion.listas-generales', [
            'licenciaturas' => $licenciaturas,
        ]);
    }
}
