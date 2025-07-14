<?php

namespace App\Livewire\Admin\Profesor;

use Livewire\Component;

class CredencialProfesor extends Component
{
    public $query = '';
    public $profesores = [];
    public $selectedIndex = 0;
    public $profesoresSeleccionados = []; // <= aquí cambia


    public $query_profesor_estudiante = '';
    public $profesores_estudiante = [];
    public $selectedIndexProfesorEstudiante = 0;
    public $profesoresEstudianteSeleccionados = []; // <- corregido nombre
    public function updatedQuery()
    {
        $this->buscarProfesores();
    }

    public function buscarProfesores()
    {
        if (strlen($this->query) > 0) {
           $this->profesores = \App\Models\Profesor::where('nombre', 'like', '%' . $this->query . '%')
            ->orWhere('apellido_paterno', 'like', '%' . $this->query . '%')
            ->orWhere('apellido_materno', 'like', '%' . $this->query . '%')
            ->orWhereHas('user', function ($q) {
                $q->where('CURP', 'like', '%' . $this->query . '%');
            })
            ->get();
        } else {
            $this->profesores = [];
        }

        $this->selectedIndex = 0;
    }

    public function selectProfesor($index)
    {
        if (isset($this->profesores[$index])) {
            $profesor = $this->profesores[$index];

            // Evitar duplicados
            if (!collect($this->profesoresSeleccionados)->contains('id', $profesor['id'])) {
                $this->profesoresSeleccionados[] = $profesor;
            }

            $this->query = ''; // limpiar el input
            $this->profesores = [];
        } else {
            $this->dispatch('swal', [
                'title' => 'Profesor no encontrado',
                'icon' => 'error',
                'position' => 'top',
            ]);
        }
    }

    public function eliminarProfesorSeleccionado($id)
    {
        $this->profesoresSeleccionados = array_filter($this->profesoresSeleccionados, function ($a) use ($id) {
            return $a['id'] !== $id;
        });
    }

    public function selectIndexUp()
    {
        if ($this->profesores) {
            $this->selectedIndex = ($this->selectedIndex - 1 + count($this->profesores)) % count($this->profesores);
        }
    }

    public function selectIndexDown()
    {
        if ($this->profesores) {
            $this->selectedIndex = ($this->selectedIndex + 1) % count($this->profesores);
        }
    }


    // CREDENCIAL DEL PROFESOR COMO ESTUDIANTE


   public function updatedQueryProfesorEstudiante()
{
    $this->buscarProfesoresEstudiante();
}

public function buscarProfesoresEstudiante()
{
    if (strlen($this->query_profesor_estudiante) > 0) {
        $this->profesores_estudiante = \App\Models\Profesor::with('user')
            ->where('nombre', 'like', '%' . $this->query_profesor_estudiante . '%')
            ->orWhere('apellido_paterno', 'like', '%' . $this->query_profesor_estudiante . '%')
            ->orWhere('apellido_materno', 'like', '%' . $this->query_profesor_estudiante . '%')
            ->orWhereHas('user', function ($q) {
                $q->where('CURP', 'like', '%' . $this->query_profesor_estudiante . '%');
            })
            ->get()
            ->toArray(); // Para trabajar con arrays
    } else {
        $this->profesores_estudiante = [];
    }

    $this->selectedIndexProfesorEstudiante = 0;
}

public function selectProfesorEstudiante($index)
{
    if (isset($this->profesores_estudiante[$index])) {
        $profesor = $this->profesores_estudiante[$index];

        if (!collect($this->profesoresEstudianteSeleccionados)->contains('id', $profesor['id'])) {
            $this->profesoresEstudianteSeleccionados[] = $profesor;
        }

        $this->query_profesor_estudiante = '';
        $this->profesores_estudiante = [];
    } else {
        $this->dispatch('swal', [
            'title' => 'Profesor no encontrado',
            'icon' => 'error',
            'position' => 'top',
        ]);
    }
}

public function eliminarProfesorEstudianteSeleccionado($id)
{
    $this->profesoresEstudianteSeleccionados = array_filter($this->profesoresEstudianteSeleccionados, function ($a) use ($id) {
        return $a['id'] !== $id;
    });
}

public function selectIndexProfesorEstudianteUp()
{
    if ($this->profesores_estudiante) {
        $this->selectedIndexProfesorEstudiante = ($this->selectedIndexProfesorEstudiante - 1 + count($this->profesores_estudiante)) % count($this->profesores_estudiante);
    }
}

public function selectIndexProfesorEstudianteDown()
{
    if ($this->profesores_estudiante) {
        $this->selectedIndexProfesorEstudiante = ($this->selectedIndexProfesorEstudiante + 1) % count($this->profesores_estudiante);
    }
}




    public function render()
    {
        return view('livewire.admin.profesor.credencial-profesor');
    }
}
