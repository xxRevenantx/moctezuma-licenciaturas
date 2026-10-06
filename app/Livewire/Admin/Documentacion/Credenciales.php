<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Cuatrimestre;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;

class Credenciales extends Component
{
    public string $modo = 'alumno';
    public string $query = '';
    public string $licenciaturaId = '';
    public string $generacionId = '';
    public string $cuatrimestreId = '';
    public array $alumnosSeleccionadosIds = [];

    public function updatedModo(): void
    {
        $this->query = '';
        $this->licenciaturaId = '';
        $this->generacionId = '';
        $this->cuatrimestreId = '';
        $this->alumnosSeleccionadosIds = [];
    }

    public function updatedLicenciaturaId(): void
    {
        $this->alumnosSeleccionadosIds = [];
    }

    public function updatedGeneracionId(): void
    {
        $this->alumnosSeleccionadosIds = [];
    }

    public function updatedCuatrimestreId(): void
    {
        $this->alumnosSeleccionadosIds = [];
    }

    public function seleccionarAlumno(int $id): void
    {
        if (! in_array($id, $this->alumnosSeleccionadosIds, true)) {
            $this->alumnosSeleccionadosIds[] = $id;
        }

        if ($this->modo === 'alumno') {
            $this->query = '';
        }
    }

    public function alternarAlumno(int $id): void
    {
        if (in_array($id, $this->alumnosSeleccionadosIds, true)) {
            $this->alumnosSeleccionadosIds = array_values(array_filter(
                $this->alumnosSeleccionadosIds,
                fn ($alumnoId) => (int) $alumnoId !== $id
            ));

            return;
        }

        $this->alumnosSeleccionadosIds[] = $id;
    }

    public function eliminarAlumnoSeleccionado(int $id): void
    {
        $this->alumnosSeleccionadosIds = array_values(array_filter(
            $this->alumnosSeleccionadosIds,
            fn ($alumnoId) => (int) $alumnoId !== $id
        ));
    }

    public function seleccionarTodosResultados(): void
    {
        $ids = $this->consultaResultados()->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->alumnosSeleccionadosIds = array_values(array_unique([
            ...$this->alumnosSeleccionadosIds,
            ...$ids,
        ]));
    }

    public function limpiarSeleccion(): void
    {
        $this->alumnosSeleccionadosIds = [];
    }

    public function restablecer(): void
    {
        $this->modo = 'alumno';
        $this->query = '';
        $this->licenciaturaId = '';
        $this->generacionId = '';
        $this->cuatrimestreId = '';
        $this->alumnosSeleccionadosIds = [];
    }

    private function consultaBase(): Builder
    {
        return Inscripcion::query()
            ->with(['licenciatura', 'generacion', 'cuatrimestre', 'modalidad'])
            ->where('status', 'true');
    }

    private function consultaResultados(): Builder
    {
        $query = $this->consultaBase();

        switch ($this->modo) {
            case 'licenciatura':
                if ($this->licenciaturaId === '') {
                    return $query->whereRaw('1 = 0');
                }
                $query->where('licenciatura_id', $this->licenciaturaId);
                break;

            case 'generacion':
                if ($this->generacionId === '') {
                    return $query->whereRaw('1 = 0');
                }
                $query->where('generacion_id', $this->generacionId);
                break;

            case 'cuatrimestre':
                if ($this->cuatrimestreId === '') {
                    return $query->whereRaw('1 = 0');
                }
                $query->where('cuatrimestre_id', $this->cuatrimestreId);
                break;

            default:
                if (mb_strlen(trim($this->query)) < 2) {
                    return $query->whereRaw('1 = 0');
                }
                break;
        }

        $busqueda = trim($this->query);
        if ($busqueda !== '') {
            $terminos = preg_split('/\s+/', $busqueda, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($terminos as $termino) {
                $query->where(function (Builder $q) use ($termino) {
                    $q->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('apellido_paterno', 'like', "%{$termino}%")
                        ->orWhere('apellido_materno', 'like', "%{$termino}%")
                        ->orWhere('CURP', 'like', "%{$termino}%")
                        ->orWhere('matricula', 'like', "%{$termino}%")
                        ->orWhere('matricula_interna', 'like', "%{$termino}%");
                });
            }
        }

        return $query
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre');
    }

    private function resultados(): Collection
    {
        return $this->consultaResultados()->get();
    }

    private function seleccionados(): Collection
    {
        if (empty($this->alumnosSeleccionadosIds)) {
            return collect();
        }

        $orden = array_flip(array_map('intval', $this->alumnosSeleccionadosIds));

        return $this->consultaBase()
            ->whereIn('id', $this->alumnosSeleccionadosIds)
            ->get()
            ->sortBy(fn (Inscripcion $alumno) => $orden[$alumno->id] ?? PHP_INT_MAX)
            ->values();
    }

    public function render()
    {
        $licenciaturas = Licenciatura::query()
            ->whereHas('inscripciones', fn (Builder $query) => $query->where('status', 'true'))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'nombre_corto']);

        $generaciones = Generacion::query()
            ->whereHas('inscripcion', fn (Builder $query) => $query->where('status', 'true'))
            ->orderByDesc('generacion')
            ->get(['id', 'generacion', 'activa']);

        $cuatrimestres = Cuatrimestre::query()
            ->whereHas('inscripciones', fn (Builder $query) => $query->where('status', 'true'))
            ->orderBy('id')
            ->get(['id', 'cuatrimestre', 'nombre_cuatrimestre']);

        return view('livewire.admin.documentacion.credenciales', [
            'licenciaturas' => $licenciaturas,
            'generaciones' => $generaciones,
            'cuatrimestres' => $cuatrimestres,
            'resultados' => $this->resultados(),
            'seleccionados' => $this->seleccionados(),
        ]);
    }
}
