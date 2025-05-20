<?php

namespace App\Livewire\Admin\Profesor;

use App\Exports\ProfesorExport;
use App\Models\Profesor;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class MostrarProfesores extends Component
{

    public $filtrar_status;
    public $search;

    public $selected = [];
    public $selectAll = false;


       public function updatedSelectAll($value)
{
    if ($value) {
        $query = Profesor::query();

        if ($this->filtrar_status) {

            if($this->filtrar_status == "Activo"){
                $query->whereHas('user', function ($query) {
                    $query->where('status', "true");
                });

            }else{
                $query->whereHas('user', function ($query) {
                $query->where('status', "false");

             });
            }
        }




        if ($this->search) {
            $query->where(function ($query) {

            $query
                ->where('nombre', 'like', '%' . $this->search . '%')
                ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                ->orWhere('telefono', 'like', '%' . $this->search . '%')
                ->orWhere('perfil', 'like', '%' . $this->search . '%')
                ->orWhereHas('user', function ($query) {
                    $query->where('email', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('user', function ($query) {
                    $query->where('CURP', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('user', function ($query) {
                     $query->where('status', "true")->whereRaw('? like ?', ['Activo',  $this->search]);

                })
                ->orWhereHas('user', function ($query) {
                    $query->where('status', "false")->whereRaw('? like ?', ['Inactivo', $this->search]);
                })
                ;
            });
        }




        $this->selected = $query->pluck('id')->toArray();
    } else {
        $this->selected = [];
    }
}


    public function getProfesoresProperty()
    {

        $query = Profesor::orderBy('apellido_paterno', 'asc');

        if ($this->filtrar_status) {

            if($this->filtrar_status == "Activo"){
                $query->whereHas('user', function ($query) {
                    $query->where('status', "true");
                });

            }else{
                $query->whereHas('user', function ($query) {
                $query->where('status', "false");

             });
            }
        }



        if ($this->search) {
            $query->where(function ($query) {

            $query
                ->where('nombre', 'like', '%' . $this->search . '%')
                ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                ->orWhere('telefono', 'like', '%' . $this->search . '%')
                ->orWhere('perfil', 'like', '%' . $this->search . '%')
                ->orWhereHas('user', function ($query) {
                    $query->where('email', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('user', function ($query) {
                    $query->where('CURP', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('user', function ($query) {
                     $query->where('status', "true")->whereRaw('? like ?', ['Activo',  $this->search]);

                })
                ->orWhereHas('user', function ($query) {
                    $query->where('status', "false")->whereRaw('? like ?', ['Inactivo', $this->search]);
                })
                ;
            });
        }

        return $query->paginate(15);
    }


    public function exportarProfesores()
    {
        $query = Profesor::query();

        // Aplicar filtros de búsqueda
        if ($this->search) {
            $query->where(function ($query) {
            $query
                ->where('nombre', 'like', '%' . $this->search . '%')
                ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                ->orWhere('telefono', 'like', '%' . $this->search . '%')
                ->orWhere('perfil', 'like', '%' . $this->search . '%')
                ->orWhereHas('user', function ($query) {
                $query->where('email', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('user', function ($query) {
                $query->where('CURP', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('user', function ($query) {
                $query->where('status', "true")->whereRaw('? like ?', ['Activo',  $this->search]);
                })
                ->orWhereHas('user', function ($query) {
                $query->where('status', "false")->whereRaw('? like ?', ['Inactivo', $this->search]);
                });
            });
        }

        // Aplicar filtro de status
        if ($this->filtrar_status) {
            if ($this->filtrar_status == "Activo") {
            $query->whereHas('user', function ($query) {
                $query->where('status', "true");
            });
            } else {
            $query->whereHas('user', function ($query) {
                $query->where('status', "false");
            });
            }
        }

        // Si hay seleccionados, filtrar por ellos
        if (!empty($this->selected)) {
            $query->whereIn('id', $this->selected);
        }

        $profesores_filtrados = $query->orderBy('apellido_paterno', 'asc')->get();

        return Excel::download(new ProfesorExport($profesores_filtrados), 'profesores_filtrados.xlsx');

    }


    public function limpiarFiltros()
    {
        $this->reset(['filtrar_status', 'search']);
    }

    #[On('refreshProfesor')]
    public function render()
    {
        return view('livewire.admin.profesor.mostrar-profesores', [
            'profesores' => $this->profesores,
        ]);
    }
}
