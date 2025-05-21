<?php

namespace App\Livewire\Admin\Materia;

use App\Models\Cuatrimestre;
use App\Models\Licenciatura;
use App\Models\Materia;
use Livewire\Component;
use Illuminate\Support\Str;


class CrearMateria extends Component
{
    public $nombre;
    public $slug;
    public $clave;
    public $creditos;
    public $cuatrimestre_id;
    public $licenciatura_id;

    public $cuatrimestres;
    public $licenciaturas;






    public function updatedNombre($value){
        $this->slug = Str::slug($value);
    }


    public function mount(){
        $this->cuatrimestres = Cuatrimestre::all();
        $this->licenciaturas = Licenciatura::all();
    }


    public function guardarMateria(){
        $this->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:materias,slug',
            'clave' => 'nullable|string|max:255|unique:materias,clave',
            'creditos' => 'nullable|integer',
            'cuatrimestre_id' => 'nullable|exists:cuatrimestres,id',
            'licenciatura_id' => 'nullable|exists:licenciaturas,id',
        ],[
            'nombre.required' => 'El nombre de la materia es obligatorio',
            'slug.required' => 'La url es obligatoria',
            'slug.unique' => 'La url ya existe',
            'clave.unique' => 'La clave ya existe',
            'creditos.integer' => 'Los creditos deben ser un número entero',
            'cuatrimestre_id.exists' => 'El cuatrimestre no existe',
            'licenciatura_id.exists' => 'La licenciatura no existe',
        ]);


         // CREAR MATERIA
        Materia::create([
            'nombre' => $this->nombre,
            'slug' => $this->slug,
            'clave' => $this->clave,
            'creditos' => $this->creditos,
            'cuatrimestre_id' => $this->cuatrimestre_id,
            'licenciatura_id' => $this->licenciatura_id,
        ]);

        $this->reset([
            'nombre',
            'slug',
            'clave',
            'creditos',
            'cuatrimestre_id',
            'licenciatura_id',
        ]);

        $this->dispatch('refreshMaterias');

        $this->dispatch('swal', [
            'title' => '¡Materia creada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);



    }




    public function render()
    {
        return view('livewire.admin.materia.crear-materia');
    }
}
