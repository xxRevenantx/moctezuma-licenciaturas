<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use Illuminate\Support\Collection;
use Livewire\Component;

class ExpedicionDocumentos extends Component
{
    /**
     * Modo de trabajo:
     * - licenciatura: permite elegir licenciatura, generación y alumnos.
     * - generacion: genera los documentos de TODAS las licenciaturas que tengan
     *   alumnos activos en la generación seleccionada.
     */
    public string $modo = 'licenciatura';

    public ?int $licenciatura_id = null;
    public ?int $generacion_id = null;

    /** @var array<int> */
    public array $alumno_ids = [];

    /** @var array<string> */
    public array $documentos = ['registro-escolaridad'];

    public string $salida = 'consolidado';
    public string $alcance_alumnos = 'seleccion';

    public string $busquedaAlumno = '';
    public string $filtroAlumno = 'todos';

    public Collection $licenciaturas;
    public Collection $generaciones;
    public Collection $generacionesGlobales;
    public Collection $alumnos;

    /** @var array{total:int,activos:int,inactivos:int} */
    public array $estadisticas = [
        'total' => 0,
        'activos' => 0,
        'inactivos' => 0,
    ];

    public function mount(): void
    {
        // IMPORTANTE: solo se muestran licenciaturas que realmente tienen
        // alumnos activos. Evita opciones vacías y expediciones sin contenido.
        $this->licenciaturas = Licenciatura::query()
            ->whereHas('inscripciones', fn ($query) => $query->where('status', 'true'))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        // Para el modo "Generación completa" solo se muestran generaciones
        // que tengan al menos un alumno activo.
        $this->generacionesGlobales = Generacion::query()
            ->whereHas('inscripcion', fn ($query) => $query->where('status', 'true'))
            ->orderByDesc('generacion')
            ->get(['id', 'generacion']);

        $this->generaciones = collect();
        $this->alumnos = collect();
    }

    public function updatedModo(): void
    {
        $this->restablecerFiltros(false);

        // En la expedición por generación completa el caso de uso principal es
        // obtener todos los Registros y todas las Actas de una sola vez.
        if ($this->modo === 'generacion') {
            $this->documentos = ['registro-escolaridad', 'acta-resultados'];
        }
    }

    public function updatedLicenciaturaId($value): void
    {
        $this->generacion_id = null;
        $this->alumno_ids = [];
        $this->alumnos = collect();
        $this->generaciones = collect();
        $this->busquedaAlumno = '';
        $this->filtroAlumno = 'todos';
        $this->alcance_alumnos = 'seleccion';
        $this->reiniciarEstadisticas();

        if (! $value) {
            return;
        }

        // La generación también debe tener alumnos activos en la licenciatura.
        $this->generaciones = Generacion::query()
            ->whereHas('inscripcion', function ($query) use ($value) {
                $query
                    ->where('licenciatura_id', (int) $value)
                    ->where('status', 'true');
            })
            ->orderByDesc('generacion')
            ->get(['id', 'generacion']);
    }

    public function updatedGeneracionId(): void
    {
        $this->alumno_ids = [];
        $this->busquedaAlumno = '';
        $this->filtroAlumno = 'todos';
        $this->alcance_alumnos = 'seleccion';

        if ($this->modo === 'licenciatura') {
            $this->cargarAlumnos();
        }
    }

    public function updatedAlumnoIds(): void
    {
        // Si el usuario cambia manualmente una selección deja de considerarse
        // "toda la generación/licenciatura" y pasa a una selección explícita.
        if (count($this->alumno_ids) !== $this->alumnos->count()) {
            $this->alcance_alumnos = 'seleccion';
        }
    }

    public function updatedBusquedaAlumno(): void
    {
        $this->filtroAlumno = 'todos';
    }

    protected function cargarAlumnos(): void
    {
        $this->alumnos = collect();
        $this->reiniciarEstadisticas();

        if (! $this->licenciatura_id || ! $this->generacion_id) {
            return;
        }

        $base = Inscripcion::query()
            ->where('licenciatura_id', $this->licenciatura_id)
            ->where('generacion_id', $this->generacion_id);

        $this->estadisticas['total'] = (clone $base)->count();
        $this->estadisticas['activos'] = (clone $base)->where('status', 'true')->count();
        $this->estadisticas['inactivos'] = max(
            0,
            $this->estadisticas['total'] - $this->estadisticas['activos']
        );

        $this->alumnos = (clone $base)
            ->select('id', 'matricula', 'CURP', 'nombre', 'apellido_paterno', 'apellido_materno')
            ->where('status', 'true')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();
    }

    public function seleccionarTodosAlumnos(): void
    {
        $this->alumno_ids = $this->alumnos->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->alcance_alumnos = 'todos';
    }

    public function seleccionarResultados(): void
    {
        $ids = $this->alumnosFiltrados
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->alumno_ids = collect($this->alumno_ids)
            ->merge($ids)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->alcance_alumnos = count($this->alumno_ids) === $this->alumnos->count()
            ? 'todos'
            : 'seleccion';
    }

    public function limpiarSeleccion(): void
    {
        $this->alumno_ids = [];
        $this->alcance_alumnos = 'seleccion';
    }

    public function restablecerTodo(): void
    {
        $this->restablecerFiltros(true);
    }

    private function restablecerFiltros(bool $reiniciarModo): void
    {
        if ($reiniciarModo) {
            $this->modo = 'licenciatura';
        }

        $this->licenciatura_id = null;
        $this->generacion_id = null;
        $this->alumno_ids = [];
        $this->documentos = ['registro-escolaridad'];
        $this->salida = 'consolidado';
        $this->alcance_alumnos = 'seleccion';
        $this->busquedaAlumno = '';
        $this->filtroAlumno = 'todos';
        $this->generaciones = collect();
        $this->alumnos = collect();
        $this->reiniciarEstadisticas();
    }

    private function reiniciarEstadisticas(): void
    {
        $this->estadisticas = [
            'total' => 0,
            'activos' => 0,
            'inactivos' => 0,
        ];
    }

    public function getAlumnosFiltradosProperty(): Collection
    {
        $busqueda = mb_strtolower(trim($this->busquedaAlumno));
        $seleccionados = collect($this->alumno_ids)->map(fn ($id) => (int) $id);

        return $this->alumnos
            ->filter(function ($alumno) use ($busqueda) {
                if ($busqueda === '') {
                    return true;
                }

                $texto = mb_strtolower(implode(' ', [
                    $alumno->nombre,
                    $alumno->apellido_paterno,
                    $alumno->apellido_materno,
                    $alumno->matricula,
                    $alumno->CURP,
                ]));

                return str_contains($texto, $busqueda);
            })
            ->filter(function ($alumno) use ($seleccionados) {
                $seleccionado = $seleccionados->contains((int) $alumno->id);

                return match ($this->filtroAlumno) {
                    'seleccionados' => $seleccionado,
                    'no-seleccionados' => ! $seleccionado,
                    default => true,
                };
            })
            ->values();
    }

    public function getLicenciaturaSeleccionadaProperty(): ?Licenciatura
    {
        if (! $this->licenciatura_id) {
            return null;
        }

        return $this->licenciaturas->firstWhere('id', $this->licenciatura_id);
    }

    public function getGeneracionSeleccionadaProperty(): ?Generacion
    {
        if (! $this->generacion_id) {
            return null;
        }

        $catalogo = $this->modo === 'generacion'
            ? $this->generacionesGlobales
            : $this->generaciones;

        return $catalogo->firstWhere('id', $this->generacion_id);
    }

    public function getResumenGeneracionCompletaProperty(): array
    {
        if ($this->modo !== 'generacion' || ! $this->generacion_id) {
            return ['alumnos' => 0, 'licenciaturas' => 0];
        }

        $query = Inscripcion::query()
            ->where('generacion_id', $this->generacion_id)
            ->where('status', 'true');

        return [
            'alumnos' => (clone $query)->count(),
            'licenciaturas' => (clone $query)->distinct()->count('licenciatura_id'),
        ];
    }

    public function render()
    {
        return view('livewire.admin.documentacion.expedicion-documentos');
    }
}
