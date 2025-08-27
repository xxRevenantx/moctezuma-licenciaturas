<?php

namespace App\Livewire\Admin\Progreso;

use Livewire\Component;

use App\Models\Licenciatura;
use App\Models\Generacion;
use App\Models\Modalidad;
use App\Models\Cuatrimestre;
use App\Models\Inscripcion;
use App\Models\AsignacionMateria;
use App\Models\Calificacion;
use Illuminate\Support\Collection;

class PanelProgresoCalificaciones extends Component
{
    // Filtros
    public ?int $licenciatura_id = null;
    public ?int $generacion_id   = null;
    public ?int $modalidad_id    = null;
    public ?int $cuatrimestre_id = null;

    // Catálogos
    public Collection $licenciaturas;
    public Collection $generaciones;
    public Collection $modalidades;
    public Collection $cuatrimestres;

    // Datos resumen global (capturadas válidas y faltantes)
    public int $esperadas = 0;
    public int $capturadas = 0;
    public int $faltantes = 0;
    public float $porcentaje = 0.0;              // % capturadas
    public float $porcentaje_faltantes = 0.0;    // % faltantes
    public ?float $promedio = null;              // promedio de numéricas válidas

    // Grids por grupo
    public array $porLicenciatura = [];
    public array $porGeneracion   = [];
    public array $porModalidad    = [];
    public array $porCuatrimestre = [];

    public function mount()
    {
        $this->licenciaturas = Licenciatura::orderBy('nombre')->get();
        $this->generaciones  = Generacion::orderBy('generacion')->get();
        $this->modalidades   = Modalidad::orderBy('nombre')->get();
        $this->cuatrimestres = Cuatrimestre::orderBy('cuatrimestre')->get();

        $this->recalcular();
    }

    public function updated($field)
    {
        if (in_array($field, ['licenciatura_id','generacion_id','modalidad_id','cuatrimestre_id'])) {
            $this->recalcular();
        }
    }

    public function limpiarFiltros()
    {
        $this->reset(['licenciatura_id','generacion_id','modalidad_id','cuatrimestre_id']);
        $this->recalcular();
    }

    /** ------------------------------- LÓGICA -------------------------------- */
    protected function filtros(): array
    {
        return [
            'licenciatura_id' => $this->licenciatura_id,
            'generacion_id'   => $this->generacion_id,
            'modalidad_id'    => $this->modalidad_id,
            'cuatrimestre_id' => $this->cuatrimestre_id,
        ];
    }

    protected function idsAlumnos(array $f): Collection
    {
        // Nota: si Inscripcion NO tiene cuatrimestre_id, elimina esa línea.
        return Inscripcion::query()
            ->when($f['licenciatura_id'], fn($q,$v)=>$q->where('licenciatura_id',$v))
            ->when($f['generacion_id'],   fn($q,$v)=>$q->where('generacion_id',$v))
            ->when($f['modalidad_id'],    fn($q,$v)=>$q->where('modalidad_id',$v))
            // ->when($f['cuatrimestre_id'], fn($q,$v)=>$q->where('cuatrimestre_id',$v))
            ->pluck('id')->unique()->values();
    }

    protected function idsAsignaciones(array $f): Collection
    {
        // SOLO materias calificables
        return AsignacionMateria::query()
            ->when($f['licenciatura_id'], fn($q,$v)=>$q->where('licenciatura_id',$v))
            ->when($f['modalidad_id'],    fn($q,$v)=>$q->where('modalidad_id',$v))
            ->when($f['cuatrimestre_id'], fn($q,$v)=>$q->where('cuatrimestre_id',$v))
            ->whereHas('materia', fn($q)=> $q->where('calificable','true'))
            ->pluck('id');
    }

    /**
     * Conteo con el mismo criterio de validez que usas en captura:
     * - Válidas: números 5–10 (incluye decimales) o "NP"
     * - Faltantes: esperadas - capturadas_válidas
     * Además: filtro doble por calificables (en asignaciones y en califs)
     */
    protected function conteos(array $f): array
    {
        $alumnoIds = $this->idsAlumnos($f);
        $asigIds   = $this->idsAsignaciones($f);

        $esperadas = $alumnoIds->count() * $asigIds->count();

        $capturadas = 0;
        $faltantes  = $esperadas;
        $promedio   = null;

        if ($esperadas > 0) {
            $califs = Calificacion::query()
                ->when($f['licenciatura_id'], fn($q,$v)=>$q->where('licenciatura_id',$v))
                ->when($f['generacion_id'],   fn($q,$v)=>$q->where('generacion_id',$v))
                ->when($f['modalidad_id'],    fn($q,$v)=>$q->where('modalidad_id',$v))
                ->when($f['cuatrimestre_id'], fn($q,$v)=>$q->where('cuatrimestre_id',$v))
                ->whereIn('alumno_id', $alumnoIds)
                ->whereIn('asignacion_materia_id', $asigIds)
                ->whereHas('asignacionMateria.materia', fn($q)=> $q->where('calificable','true')) // cinturón y tirantes
                ->pluck('calificacion');

            // Filtra únicamente válidas (5–10 o NP)
            $validas = $califs->filter(function ($v) {
                if ($v === null) return false;
                $s = trim((string)$v);
                if (strtoupper($s) === 'NP') return true;
                if (is_numeric($s)) {
                    $n = (float)$s;
                    return $n >= 5 && $n <= 10;
                }
                return false;
            });

            $capturadas = $validas->count();
            $faltantes  = max(0, $esperadas - $capturadas);

            // Promedio numérico solo con válidas numéricas
            $numericas = $validas->filter(fn($s)=>is_numeric($s))->map(fn($s)=>(float)$s);
            $promedio  = $numericas->count() ? round($numericas->avg(), 2) : null;
        }

        $porcentaje            = $esperadas > 0 ? round(($capturadas / $esperadas) * 100, 1) : 0.0;
        $porcentaje_faltantes  = $esperadas > 0 ? round(($faltantes  / $esperadas) * 100, 1) : 0.0;

        return compact('esperadas','capturadas','faltantes','porcentaje','porcentaje_faltantes','promedio');
    }

    protected function gridPorGrupo(string $grupo, Collection $catalogo, ?string $etiqueta = 'nombre'): array
    {
        $base  = $this->filtros();
        $items = [];

        foreach ($catalogo as $item) {
            $f = $base;
            $f[$grupo] = $item->id;

            $res = $this->conteos($f);

            $items[] = [
                'id'                    => $item->id,
                'label'                 => $item->{$etiqueta} ?? (string)$item->id,
                'esperadas'             => $res['esperadas'],
                'capturadas'            => $res['capturadas'],
                'faltantes'             => $res['faltantes'],
                'porcentaje'            => $res['porcentaje'],           // % capturadas
                'porcentaje_faltantes'  => $res['porcentaje_faltantes'], // % faltantes
                'promedio'              => $res['promedio'],
            ];
        }

        // Ordena por menor faltante (más completo arriba)
        usort($items, fn($a,$b)=>$a['porcentaje_faltantes'] <=> $b['porcentaje_faltantes']);

        return $items;
    }

    public function recalcular(): void
    {
        // Resumen global con filtros
        $g = $this->conteos($this->filtros());
        $this->esperadas            = $g['esperadas'];
        $this->capturadas           = $g['capturadas'];
        $this->faltantes            = $g['faltantes'];
        $this->porcentaje           = $g['porcentaje'];
        $this->porcentaje_faltantes = $g['porcentaje_faltantes'];
        $this->promedio             = $g['promedio'];

        // Grids por grupo
        $this->porLicenciatura = $this->gridPorGrupo('licenciatura_id', $this->licenciaturas, 'nombre');
        $this->porGeneracion   = $this->gridPorGrupo('generacion_id',   $this->generaciones,  'generacion');
        $this->porModalidad    = $this->gridPorGrupo('modalidad_id',    $this->modalidades,   'nombre');
        $this->porCuatrimestre = $this->gridPorGrupo('cuatrimestre_id', $this->cuatrimestres, 'cuatrimestre');
    }

    public function render()
    {
        return view('livewire.admin.progreso.panel-progreso-calificaciones');
    }
}
