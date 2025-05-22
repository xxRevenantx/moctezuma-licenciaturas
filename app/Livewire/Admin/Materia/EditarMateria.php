<?php

namespace App\Livewire\Admin\Materia;

use App\Models\Materia;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Str;

class EditarMateria extends Component
{

    public $materiaId;
    public $nombre;
    public $slug;
    public $clave;
    public $creditos;
    public $licenciatura_id;
    public $cuatrimestre_id;
    public $calificable;

    public $open = false;

    #[On('abrirMateria')]
    public function abrirModal($id)
    {
        $materia = Materia::findOrFail($id);
        $this->materiaId = $materia->id;
        $this->nombre = $materia->nombre;
        $this->slug = $materia->slug;
        $this->clave = $materia->clave;
        $this->creditos = $materia->creditos;
        $this->licenciatura_id = $materia->licenciatura_id;
        $this->cuatrimestre_id = $materia->cuatrimestre_id;
        $this->calificable = $materia->calificable == "true" ? "true" : "false";

        $this->open = true;
    }


     public function updatedNombre($value){
        $this->slug = Str::slug($value);
    }


    public function actualizarMateria(){
        $this->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:materias,slug,'.$this->materiaId,
            'clave' => 'required|string|max:255|unique:materias,clave,'.$this->materiaId,
            'calificable' => 'required|in:true,false',
            'creditos' => 'required|integer',
            'cuatrimestre_id' => 'required|exists:cuatrimestres,id',
            'licenciatura_id' => 'required|exists:licenciaturas,id',

        ],[
            'nombre.required' => 'El nombre de la materia es requerido',
            'slug.required' => 'El slug de la materia es requerido',
            'slug.unique' => 'El slug de la materia ya existe',
            'clave.required' => 'La clave de la materia es requerida',
            'clave.unique' => 'La clave de la materia ya existe',
            'creditos.required' => 'Los creditos son requeridos',
            'cuatrimestre_id.required' => 'El cuatrimestre es requerido',
            'licenciatura_id.required' => 'La licenciatura es requerida',
            'calificable.required' => 'El campo calificable es requerido',

        ]);

        $materia = Materia::findOrFail($this->materiaId);
        $materia->update([
            "nombre" => trim($this->nombre),
            "slug" => trim($this->slug),
            "clave" => trim($this->clave),
            "creditos" => $this->creditos,
            "licenciatura_id" => $this->licenciatura_id,
            "cuatrimestre_id" => $this->cuatrimestre_id,
            "calificable" => $this->calificable
        ]);

        $this->dispatch('swal', [
                'title' => '¡Materia actualizada correctamente!',
                'icon' => 'success',
                'position' => 'top-end',
            ]);

            $this->reset(['open', 'nombre', 'slug', 'clave', 'creditos', 'licenciatura_id', 'cuatrimestre_id', 'calificable']);
            $this->dispatch('refreshMaterias');
             $this->cerrarModal();
    }




     public function cerrarModal()
    {
        $this->reset([
            'materiaId',
            'nombre',
            'slug',
            'clave',
            'creditos',
            'licenciatura_id',
            'cuatrimestre_id',
            'calificable'
        ]);
          $this->resetValidation();

    }



    public function render()
    {
        $licenciaturas = \App\Models\Licenciatura::all();
        $cuatrimestres = \App\Models\Cuatrimestre::all();
        return view('livewire.admin.materia.editar-materia', [
            'licenciaturas' => $licenciaturas,
            'cuatrimestres' => $cuatrimestres
        ]);
    }
}
