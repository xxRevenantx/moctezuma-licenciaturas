<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\AsignarGeneracion;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use Livewire\Component;

class ExpedicionDocumentos extends Component
{
    /** Filtros seleccionados */
    public ?int $licenciatura_id = null;
    public ?int $generacion_id   = null;

    /** Ahora es MULTI-SELECCIÓN */
    public array $alumno_ids = [];

    /** Catálogos */
    public $licenciaturas = [];
    public $generaciones  = [];
    public $alumnos       = []; // colección/arreglo de Inscripcion (id, nombre, apellidos)

    public function mount(): void
    {
        $this->licenciaturas = Licenciatura::orderBy('nombre')->get(['id', 'nombre']);
        $this->generaciones  = [];
        $this->alumnos       = [];
    }

    /** Al cambiar la licenciatura, resetea dependientes y carga generaciones */
        public function updatedLicenciaturaId($value): void
        {
            $this->generacion_id = null;
            $this->alumno_ids    = [];   // si usas multi
            $this->alumnos       = [];
            $this->generaciones  = [];

            if (!$value) return;

            // 1) IDs únicos de generación para esa licenciatura (sin repetir por modalidad)
            $genIds = AsignarGeneracion::where('licenciatura_id', $value)
                ->distinct()
                ->pluck('generacion_id');

            // 2) Cargar las generaciones reales ordenadas por texto
            $this->generaciones = Generacion::whereIn('id', $genIds)
                ->orderBy('generacion')
                ->get(['id','generacion']);
        }

    /** Al cambiar la generación, resetea selección y carga alumnos */
    public function updatedGeneracionId($value): void
    {
        $this->alumno_ids = [];
        $this->cargarAlumnos();
    }

    /** Carga alumnos según licenciatura + generación */
    protected function cargarAlumnos(): void
    {
        $this->alumnos = [];

        if ($this->licenciatura_id && $this->generacion_id) {
            $this->alumnos = Inscripcion::select('id','nombre','apellido_paterno','apellido_materno')
                ->where('licenciatura_id', $this->licenciatura_id)
                ->where('generacion_id',   $this->generacion_id)
                ->where('status', 'true')
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->orderBy('nombre')
                ->get();
        }
    }

    /** Helpers para UX */
    public function seleccionarTodosAlumnos(): void
    {
        $this->alumno_ids = collect($this->alumnos)->pluck('id')->all();
    }

    public function limpiarSeleccion(): void
    {
        $this->alumno_ids = [];
    }

    public function render()
    {
        return view('livewire.admin.documentacion.expedicion-documentos');
    }
}
