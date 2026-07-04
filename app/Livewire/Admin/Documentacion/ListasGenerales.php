<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ListasGenerales extends Component
{
    public $licenciatura_id = '';

    public $alumnos = [];

    public $licenciatura_nombre = null;

    public $search = '';

    /**
     * null    = todos
     * 'true'  = únicamente foráneos
     * 'false' = únicamente locales
     */
    public $filtrar_foraneo = null;

    public $consultado = false;

    public $generacion_id = '';

    public $procedencia_generacion = 'todos';

    public $resumen_generacion = [];

    public $total_generacion = 0;

    public $generacion_consultada = false;

    /**
     * Consulta las listas después de validar la licenciatura
     * y el filtro de procedencia.
     */
    public function consultarListas(): void
    {
        $this->validate([
            'licenciatura_id' => [
                'required',
                'exists:licenciaturas,id',
            ],
            'filtrar_foraneo' => [
                'nullable',
                Rule::in(['true', 'false']),
            ],
        ], [
            'licenciatura_id.required' => 'Debes seleccionar una licenciatura.',
            'licenciatura_id.exists' => 'La licenciatura seleccionada no es válida.',
            'filtrar_foraneo.in' => 'El filtro de procedencia no es válido.',
        ]);

        $this->consultado = true;

        $this->cargarAlumnos();
    }

    /**
     * Carga los alumnos aplicando licenciatura,
     * procedencia y búsqueda.
     */
    private function cargarAlumnos(): void
    {
        if (!$this->consultado || !$this->licenciatura_id) {
            $this->alumnos = [];
            $this->licenciatura_nombre = null;

            return;
        }

        $this->licenciatura_nombre = Licenciatura::query()
            ->whereKey($this->licenciatura_id)
            ->value('nombre');

        $busqueda = trim((string) $this->search);

        $this->alumnos = Inscripcion::query()
            ->with([
                'licenciatura:id,nombre',
                'generacion:id,generacion,activa',
                'modalidad:id,nombre',
                'cuatrimestre:id,nombre_cuatrimestre,cuatrimestre',
            ])

            // Licenciatura seleccionada
            ->where('licenciatura_id', $this->licenciatura_id)

            // Únicamente alumnos activos
            ->where('status', 'true')

            /*
            |--------------------------------------------------------------------------
            | Filtro de procedencia
            |--------------------------------------------------------------------------
            |
            | No se filtra por generacion.activa porque existen alumnos activos
            | pertenecientes a generaciones marcadas como inactivas.
            |
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
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($consulta) use ($busqueda) {
                    $consulta
                        ->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_materno', 'like', "%{$busqueda}%")
                        ->orWhere('matricula', 'like', "%{$busqueda}%")
                        ->orWhere('CURP', 'like', "%{$busqueda}%")
                        ->orWhereRaw(
                            "CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) LIKE ?",
                            ["%{$busqueda}%"]
                        )
                        ->orWhereRaw(
                            "CONCAT_WS(' ', apellido_paterno, apellido_materno, nombre) LIKE ?",
                            ["%{$busqueda}%"]
                        );
                });
            })

            /*
            |--------------------------------------------------------------------------
            | Orden de los resultados
            |--------------------------------------------------------------------------
            */
            ->orderBy('generacion_id')
            ->orderBy('cuatrimestre_id')
            ->orderBy('modalidad_id')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Al cambiar la licenciatura se limpian los filtros.
     */
    public function updatedLicenciaturaId(): void
    {
        $this->resetValidation();

        $this->search = '';
        $this->filtrar_foraneo = null;
        $this->alumnos = [];
        $this->licenciatura_nombre = null;
        $this->consultado = false;
    }

    /**
     * Actualiza la lista al escribir en el buscador.
     */
    public function updatedSearch(): void
    {
        if ($this->consultado && $this->licenciatura_id) {
            $this->cargarAlumnos();
        }
    }

    /**
     * Actualiza automáticamente la lista al cambiar
     * entre Todos, Foráneos y Locales.
     */
    public function updatedFiltrarForaneo(): void
    {
        if ($this->consultado && $this->licenciatura_id) {
            $this->cargarAlumnos();
        }
    }


    public function consultarGeneracion(): void
    {
        $this->validate([
            'generacion_id' => ['required', 'exists:generaciones,id'],
            'procedencia_generacion' => ['required', Rule::in(['todos', 'true', 'false'])],
        ], [
            'generacion_id.required' => 'Debes seleccionar una generación.',
            'generacion_id.exists' => 'La generación seleccionada no es válida.',
            'procedencia_generacion.required' => 'Debes seleccionar la procedencia.',
            'procedencia_generacion.in' => 'La procedencia seleccionada no es válida.',
        ]);

        $conteos = Inscripcion::query()
            ->selectRaw("licenciatura_id, COUNT(*) AS total")
            ->selectRaw("SUM(CASE WHEN foraneo = 'true' THEN 1 ELSE 0 END) AS foraneos")
            ->selectRaw("SUM(CASE WHEN foraneo = 'false' THEN 1 ELSE 0 END) AS locales")
            ->where('generacion_id', $this->generacion_id)
            ->where('status', 'true')
            ->when($this->procedencia_generacion !== 'todos', function ($query) {
                $query->where('foraneo', $this->procedencia_generacion);
            })
            ->groupBy('licenciatura_id')
            ->get()
            ->keyBy('licenciatura_id');

        $this->resumen_generacion = Licenciatura::query()
            ->orderBy('id')
            ->get(['id', 'nombre'])
            ->map(function (Licenciatura $licenciatura) use ($conteos) {
                $conteo = $conteos->get($licenciatura->id);

                return [
                    'id' => $licenciatura->id,
                    'nombre' => $licenciatura->nombre,
                    'locales' => (int) ($conteo->locales ?? 0),
                    'foraneos' => (int) ($conteo->foraneos ?? 0),
                    'total' => (int) ($conteo->total ?? 0),
                ];
            })
            ->all();

        $this->total_generacion = collect($this->resumen_generacion)->sum('total');
        $this->generacion_consultada = true;
    }

    public function updatedGeneracionId(): void
    {
        $this->resetValidation(['generacion_id']);
        $this->resumen_generacion = [];
        $this->total_generacion = 0;
        $this->generacion_consultada = false;
    }

    public function updatedProcedenciaGeneracion(): void
    {
        if ($this->generacion_consultada && $this->generacion_id) {
            $this->consultarGeneracion();
        }
    }

    /**
     * Actualiza la lista después de editar un alumno.
     */
    #[On('refreshMatricula')]
    public function refrescarListas(): void
    {
        if ($this->consultado && $this->licenciatura_id) {
            $this->cargarAlumnos();
        }
    }

    public function render()
    {
        return view('livewire.admin.documentacion.listas-generales', [
            'licenciaturas' => Licenciatura::query()
                ->orderBy('nombre')
                ->get(),
            'generaciones' => Generacion::query()
                ->orderBy('id')
                ->get(['id', 'generacion', 'activa']),
        ]);
    }
}
