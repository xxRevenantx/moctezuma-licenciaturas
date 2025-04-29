<?php

namespace App\Livewire\Admin\Licenciatura;

use App\Helpers\Flash;
use App\Models\Licenciatura;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class CrearLicenciatura extends Component
{

    use WithFileUploads;

    public $nombre;
    public $RVOE;
    public $nombre_corto;
    public $imagen;
    public $slug;



    public function updatedNombre($value)
    {
        // Genera el slug automáticamente cuando el título cambia
        $this->slug = Str::slug(trim($value));

    }

    public function guardarLicenciatura()
    {

        $this->validate([
            'nombre' => 'required|unique:licenciaturas',
            'RVOE' => 'nullable|unique:licenciaturas',
            'nombre_corto' => 'required|unique:licenciaturas',
            'imagen' =>  'image|nullable|max:2048|mimes:jpeg,jpg,png',
            'slug' => 'required|unique:licenciaturas,slug',
        ],[
            'nombre.required' => 'El nombre de la licenciatura es obligatorio.',
            'nombre.unique' => 'El nombre de la licenciatura ya existe.',
            'RVOE.unique' => 'El RVOE ya existe.',
            'nombre_corto.required' => 'El nombre corto es obligatorio.',
            'nombre_corto.unique' => 'El nombre corto ya existe.',
            'slug.required' => 'La url es obligatoria.',
            'slug.unique' => 'La url ya existe.',
            'imagen.image' => 'El archivo debe ser una imagen',
            'imagen.max' => 'El archivo no debe pesar más de 2MB',
            'imagen.mimes' => 'El archivo debe ser formato jpeg, jpg o png',
        ]);

        if ($this->imagen) {
            $imagen = $this->imagen->store('licenciaturas');
            $datos["imagen"] = str_replace('licenciaturas/', '', $imagen);
        } else {
            $datos["imagen"] = null;
        }

        Licenciatura::create([
            'nombre' => trim($this->nombre),
            'RVOE' => $this->RVOE,
            'nombre_corto' => trim($this->nombre_corto),
            'slug' => $this->slug,
            'imagen' => $datos["imagen"],
        ]);

        $this->reset(['nombre', 'RVOE', 'nombre_corto', 'imagen', 'slug']);


        $this->dispatch('swal', [
            'title' => '¡Licenciatura creada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        $this->dispatch('refreshLicenciaturas'); // Emitir el evento para refrescar la lista de licenciaturas
    }





    public static function placeholder(){
        return view('placeholder');

    }

    public function render()
    {
        return view('livewire.admin.licenciatura.crear-licenciatura');

    }
}
