<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Inscripcion;
use App\Models\Licenciatura;
use Livewire\Component;

class ListasGenerales extends Component
{
    public $licenciatura_id = null;

    public $alumnos = [];

    public ?string $licenciatura_nombre = null;

    public string $search = '';

    /*
    |--------------------------------------------------------------------------
    | Filtro de procedencia
    |--------------------------------------------------------------------------
    |
    | todos = Mostrar todos
    | true  = Mostrar únicamente foráneos
    | false = Mostrar únicamente locales
    |
    */
    public string $filtrar_foraneo = 'todos';

    /**
     * Indica si ya se realizó una consulta.
     */
    public bool $consultado = false;

    /**
     * Consulta inicial mediante el botón.
     */
    public function consultarListas(): void
    {
        $this->validate([
            'licenciatura_id' => [
                'required',
                'exists:licenciaturas,id',
            ],
            'filtrar_foraneo' => [
                'required',
                'in:todos,true,false',
            ],
        ], [
            'licenciatura_id.required' => 'Debes seleccionar una licenciatura.',
            'licenciatura_id.exists' => 'La licenciatura seleccionada no es válida.',
            'filtrar_foraneo.required' => 'Debes seleccionar un filtro.',
            'filtrar_foraneo.in' => 'El filtro seleccionado no es válido.',
        ]);

        $this->consultado = true;

        $this->cargarAlumnos();
    }

    /**
     * Carga los alumnos aplicando todos los filtros.
     */
    private function cargarAlumnos(): void
    {
        if (!$this->licenciatura_id) {
            $this->alumnos = [];
            $this->licenciatura_nombre = null;

            return;
        }

        $this->licenciatura_nombre = Licenciatura::query()
            ->whereKey($this->licenciatura_id)
            ->value('nombre');

        $search = trim($this->search);

        $this->alumnos = Inscripcion::query()
            ->where('licenciatura_id', $this->licenciatura_id)
            ->where('status', 'true')
            ->whereHas('generacion', function ($query) {
                $query->where('activa', 'true');
            })

            /*
            |--------------------------------------------------------------------------
            | Filtro foráneo/local
            |--------------------------------------------------------------------------
            */
            ->when(
                in_array($this->filtrar_foraneo, ['true', 'false'], true),
                function ($query) {
                    $query->where('foraneo', $this->filtrar_foraneo);
                }
            )

            /*
            |--------------------------------------------------------------------------
            | Buscador
            |--------------------------------------------------------------------------
            */
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($consulta) use ($search) {
                    $consulta
                        ->where('nombre', 'like', '%' . $search . '%')
                        ->orWhere('apellido_paterno', 'like', '%' . $search . '%')
                        ->orWhere('apellido_materno', 'like', '%' . $search . '%')
                        ->orWhere('matricula', 'like', '%' . $search . '%')
                        ->orWhereRaw(
                            "CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) LIKE ?",
                            ['%' . $search . '%']
                        )
                        ->orWhereRaw(
                            "CONCAT_WS(' ', apellido_paterno, apellido_materno, nombre) LIKE ?",
                            ['%' . $search . '%']
                        );
                });
            })
            ->with([
                'licenciatura',
                'generacion',
                'modalidad',
                'cuatrimestre',
            ])
            ->orderBy('generacion_id')
            ->orderBy('cuatrimestre_id')
            ->orderBy('modalidad_id')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Al cambiar de licenciatura se restablecen los filtros.
     */
    public function updatedLicenciaturaId(): void
    {
        $this->resetValidation();

        $this->search = '';
        $this->filtrar_foraneo = 'todos';
        $this->alumnos = [];
        $this->licenciatura_nombre = null;
        $this->consultado = false;
    }

    /**
     * Buscador reactivo.
     */
    public function updatedSearch(): void
    {
        if ($this->consultado && $this->licenciatura_id) {
            $this->cargarAlumnos();
        }
    }

    /**
     * Filtro foráneo/local reactivo.
     */
    public function updatedFiltrarForaneo(): void
    {
        if ($this->consultado && $this->licenciatura_id) {
            $this->cargarAlumnos();
        }
    }

    public function render()
    {
        $licenciaturas = Licenciatura::query()
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.documentacion.listas-generales', [
            'licenciaturas' => $licenciaturas,
        ]);
    }
}
