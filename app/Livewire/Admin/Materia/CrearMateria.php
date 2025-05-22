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
    public $calificable;
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
            'clave' => 'required|string|max:255|unique:materias,clave',
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
            'calificable.in' => 'El campo calificable debe ser true o false',
            'creditos.integer' => 'Los creditos deben ser un numero entero',
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
