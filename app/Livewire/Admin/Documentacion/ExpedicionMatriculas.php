<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Modalidad;
use App\Services\Matriculas\ExpedicionMatriculasService;
use Illuminate\Support\Collection;
use Livewire\Component;

class ExpedicionMatriculas extends Component
{
    public string $generacionId = '';
    public array $licenciaturaIds = [];
    public string $estado = 'activos';
    public string $seg = 'pendientes';
    public string $modalidadId = '';
    public string $busqueda = '';
    public string $mesMasivo = '';
    public string $observacionMasiva = '';
    public array $alumnoIds = [];
    public array $meses = [];
    public array $observaciones = [];
    public string $plantilla = 'seg';
    public string $salida = 'consolidado';
    public array $columnasSeleccionadas = ExpedicionMatriculasService::COLUMNAS_PREDETERMINADAS;
    public array $ordenColumnas = ['numero', 'nombre', 'apellido_paterno', 'apellido_materno', 'curp', 'modalidad', 'mes_ingreso', 'observaciones', 'matricula_seg', 'matricula_interna', 'cuatrimestre', 'estado', 'fecha_inscripcion'];

    public function updatedGeneracionId(): void
    {
        $this->licenciaturaIds = $this->generacionId !== ''
            ? app(ExpedicionMatriculasService::class)->licenciaturasDisponibles((int) $this->generacionId)
                ->pluck('id')->map(fn ($id) => (string) $id)->all()
            : [];
        $this->recalcularAlumnos();
    }

    public function updatedLicenciaturaIds(): void { $this->recalcularAlumnos(); }
    public function updatedEstado(): void { $this->recalcularAlumnos(); }
    public function updatedSeg(): void { $this->recalcularAlumnos(); }
    public function updatedModalidadId(): void { $this->recalcularAlumnos(); }

    private function alumnosDisponibles(): Collection
    {
        if ($this->generacionId === '' || $this->licenciaturaIds === []) {
            return collect();
        }
        return app(ExpedicionMatriculasService::class)->alumnos(
            (int) $this->generacionId, $this->licenciaturaIds, $this->estado, $this->seg,
            $this->modalidadId !== '' ? (int) $this->modalidadId : null
        );
    }

    private function recalcularAlumnos(): void
    {
        $this->alumnoIds = $this->alumnosDisponibles()->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->meses = [];
        $this->observaciones = [];
        $this->busqueda = '';
    }

    public function seleccionarTodos(): void
    {
        $this->alumnoIds = $this->alumnosDisponibles()->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function limpiarAlumnos(): void { $this->alumnoIds = []; }

    public function aplicarMesMasivo(): void
    {
        if (! in_array($this->mesMasivo, ExpedicionMatriculasService::MESES, true)) {
            return;
        }
        foreach ($this->alumnoIds as $id) {
            $this->meses[$id] = $this->mesMasivo;
        }
    }

    public function aplicarObservacionMasiva(): void
    {
        $texto = trim($this->observacionMasiva);
        if (mb_strlen($texto) > 500) {
            return;
        }
        foreach ($this->alumnoIds as $id) {
            $this->observaciones[$id] = $texto;
        }
    }

    public function moverAlumno(int $id, int $direccion): void
    {
        if (! in_array($direccion, [-1, 1], true)) {
            return;
        }
        $pos = array_search((string) $id, array_map('strval', $this->alumnoIds), true);
        $destino = $pos === false ? -1 : $pos + $direccion;
        if ($destino < 0 || $destino >= count($this->alumnoIds)) {
            return;
        }
        [$this->alumnoIds[$pos], $this->alumnoIds[$destino]] = [$this->alumnoIds[$destino], $this->alumnoIds[$pos]];
    }

    public function moverColumna(string $columna, int $direccion): void
    {
        $pos = array_search($columna, $this->ordenColumnas, true);
        if ($pos === false || ! in_array($direccion, [-1, 1], true)) {
            return;
        }
        $nuevo = $pos + $direccion;
        if ($nuevo < 0 || $nuevo >= count($this->ordenColumnas)) {
            return;
        }
        [$this->ordenColumnas[$pos], $this->ordenColumnas[$nuevo]] = [$this->ordenColumnas[$nuevo], $this->ordenColumnas[$pos]];
    }

    public function restaurarColumnas(): void
    {
        $this->columnasSeleccionadas = ExpedicionMatriculasService::COLUMNAS_PREDETERMINADAS;
        $this->ordenColumnas = array_keys(ExpedicionMatriculasService::COLUMNAS);
    }

    public function render()
    {
        $servicio = app(ExpedicionMatriculasService::class);
        $licenciaturas = $this->generacionId !== ''
            ? $servicio->licenciaturasDisponibles((int) $this->generacionId) : collect();
        $alumnos = $this->alumnosDisponibles();
        $ordenSeleccion = array_flip(array_map('strval', $this->alumnoIds));
        $ordenBase = array_flip($alumnos->pluck('id')->map(fn ($id) => (string) $id)->all());
        $alumnos = $alumnos->sortBy(fn ($a) => $ordenSeleccion[(string) $a->id] ?? (count($ordenSeleccion) + ($ordenBase[(string) $a->id] ?? 0)))->values();
        $consulta = mb_strtolower(trim($this->busqueda), 'UTF-8');
        $visibles = $consulta === '' ? $alumnos : $alumnos->filter(function ($alumno) use ($consulta) {
            $texto = mb_strtolower(implode(' ', [
                $alumno->nombre, $alumno->apellido_paterno, $alumno->apellido_materno,
                $alumno->CURP, $alumno->matricula, $alumno->matricula_interna,
            ]), 'UTF-8');
            return str_contains($texto, $consulta);
        });

        $duplicados = $alumnos->filter(fn ($a) => trim((string) $a->CURP) !== '')
            ->groupBy(fn ($a) => mb_strtoupper(trim((string) $a->CURP), 'UTF-8'))
            ->filter(fn ($g) => $g->count() > 1)->count();

        return view('livewire.admin.documentacion.expedicion-matriculas', [
            'generaciones' => $servicio->generaciones(),
            'licenciaturas' => $licenciaturas,
            'modalidades' => Modalidad::query()->orderBy('nombre')->get(['id', 'nombre']),
            'alumnos' => $alumnos,
            'alumnosVisibles' => $visibles->take(100),
            'totalVisibles' => $visibles->count(),
            'curpsRepetidas' => $duplicados,
            'columnasDisponibles' => ExpedicionMatriculasService::COLUMNAS,
            'columnasOrdenadas' => array_values(array_filter($this->ordenColumnas,
                fn ($col) => in_array($col, $this->columnasSeleccionadas, true))),
            'mesesDisponibles' => ExpedicionMatriculasService::MESES,
        ]);
    }
}
