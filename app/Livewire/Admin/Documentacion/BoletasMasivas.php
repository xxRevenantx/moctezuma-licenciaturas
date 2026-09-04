<?php

namespace App\Livewire\Admin\Documentacion;

use App\Mail\BoletasAlumnoMail;
use App\Services\Boletas\BoletaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class BoletasMasivas extends Component
{
    public $licenciaturaId = null;
    public $modalidadId = null;
    public $generacionId = null;
    public string $cuatrimestreId = 'todos';

    public string $modo = 'generacion';
    public string $search = '';
    public string $filtroEstado = 'todos';
    public string $formato = 'consolidado';
    public bool $incluirIncompletas = false;

    public array $licenciaturas = [];
    public array $modalidades = [];
    public array $generaciones = [];
    public array $cuatrimestres = [];
    public array $alumnos = [];
    public array $seleccionados = [];

    public array $analisis = [
        'alumnos' => [],
        'resumen' => [
            'alumnos' => 0,
            'completas' => 0,
            'incompletas' => 0,
            'sin_calificaciones' => 0,
            'boletas_posibles' => 0,
            'boletas_completas' => 0,
            'boletas_incompletas' => 0,
            'boletas_sin_calificaciones' => 0,
        ],
    ];

    public function mount(BoletaService $boletas): void
    {
        $this->licenciaturas = $boletas->licenciaturasConAlumnos()->toArray();

        $licenciatura = request()->integer('licenciatura');
        $modalidad = request()->integer('modalidad');
        $generacion = request()->integer('generacion');
        $cuatrimestre = request()->integer('cuatrimestre');

        if ($licenciatura && collect($this->licenciaturas)->contains('id', $licenciatura)) {
            $this->licenciaturaId = $licenciatura;
            $this->cargarModalidades($boletas);
        }

        if ($modalidad && collect($this->modalidades)->contains('id', $modalidad)) {
            $this->modalidadId = $modalidad;
            $this->cargarGeneraciones($boletas);
        }

        if ($generacion && collect($this->generaciones)->contains('id', $generacion)) {
            $this->generacionId = $generacion;
            $this->cargarCuatrimestres($boletas);
            $this->cargarAlumnos($boletas);
        }

        if ($cuatrimestre && collect($this->cuatrimestres)->contains('cuatrimestre_id', $cuatrimestre)) {
            $this->cuatrimestreId = (string) $cuatrimestre;
            $this->modo = 'cuatrimestre';
        }

        if ($this->generacionId) {
            $this->seleccionarTodos();
            $this->recalcularAnalisis($boletas);
        }
    }

    public function updatedLicenciaturaId($value): void
    {
        $boletas = app(BoletaService::class);
        $this->modalidadId = null;
        $this->generacionId = null;
        $this->cuatrimestreId = 'todos';
        $this->modalidades = [];
        $this->generaciones = [];
        $this->cuatrimestres = [];
        $this->alumnos = [];
        $this->seleccionados = [];
        $this->search = '';
        $this->resetAnalisis();

        if ($value) {
            $this->cargarModalidades($boletas);
        }
    }

    public function updatedModalidadId($value): void
    {
        $boletas = app(BoletaService::class);
        $this->generacionId = null;
        $this->cuatrimestreId = 'todos';
        $this->generaciones = [];
        $this->cuatrimestres = [];
        $this->alumnos = [];
        $this->seleccionados = [];
        $this->search = '';
        $this->resetAnalisis();

        if ($value) {
            $this->cargarGeneraciones($boletas);
        }
    }

    public function updatedGeneracionId($value): void
    {
        $boletas = app(BoletaService::class);
        $this->cuatrimestreId = 'todos';
        $this->cuatrimestres = [];
        $this->alumnos = [];
        $this->seleccionados = [];
        $this->search = '';
        $this->resetAnalisis();

        if (!$value) {
            return;
        }

        $this->cargarCuatrimestres($boletas);
        $this->cargarAlumnos($boletas);

        if (in_array($this->modo, ['generacion', 'cuatrimestre'], true)) {
            $this->seleccionarTodos();
        }

        $this->recalcularAnalisis($boletas);
    }

    public function updatedCuatrimestreId($value): void
    {
        $boletas = app(BoletaService::class);
        if ($this->modo === 'cuatrimestre' && $value === 'todos') {
            $primero = collect($this->cuatrimestres)->first();
            $this->cuatrimestreId = $primero ? (string) $primero['cuatrimestre_id'] : 'todos';
        }

        $this->recalcularAnalisis($boletas);
    }

    public function updatedModo($value): void
    {
        $boletas = app(BoletaService::class);
        $this->filtroEstado = 'todos';

        if ($value === 'generacion') {
            $this->cuatrimestreId = 'todos';
            $this->seleccionarTodos();
        } elseif ($value === 'cuatrimestre') {
            if ($this->cuatrimestreId === 'todos') {
                $primero = collect($this->cuatrimestres)->first();
                $this->cuatrimestreId = $primero ? (string) $primero['cuatrimestre_id'] : 'todos';
            }
            $this->seleccionarTodos();
        } elseif ($value === 'alumno') {
            $this->seleccionados = array_slice(array_values($this->seleccionados), 0, 1);
        }

        if ($this->generacionId) {
            $this->recalcularAnalisis($boletas);
        }
    }

    public function toggleAlumno(int $id): void
    {
        if (!collect($this->alumnos)->contains('id', $id)) {
            return;
        }

        if ($this->modo === 'alumno') {
            $this->seleccionados = [$id];
            return;
        }

        $actual = collect($this->seleccionados)->map(fn ($v) => (int) $v);
        if ($actual->contains($id)) {
            $this->seleccionados = $actual->reject(fn ($v) => $v === $id)->values()->all();
        } else {
            $this->seleccionados = $actual->push($id)->unique()->values()->all();
        }
    }

    public function seleccionarTodos(): void
    {
        $this->seleccionados = collect($this->alumnos)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function seleccionarResultados(): void
    {
        $ids = $this->alumnosVisibles()
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        if ($this->modo === 'alumno') {
            $primero = $ids->first();
            $this->seleccionados = $primero ? [(int) $primero] : [];
            return;
        }

        $this->seleccionados = collect($this->seleccionados)
            ->merge($ids)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function limpiarSeleccion(): void
    {
        $this->seleccionados = [];
    }

    public function enviarSeleccionados(): void
    {
        if (!$this->licenciaturaId || !$this->modalidadId || !$this->generacionId) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Completa los filtros académicos.',
                'position' => 'top-end',
            ]);
            return;
        }

        $seleccionados = collect($this->seleccionados)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($seleccionados->isEmpty()) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Selecciona al menos un alumno.',
                'position' => 'top-end',
            ]);
            return;
        }

        $cuatrimestreIds = $this->cuatrimestreIdsActuales();
        if ($cuatrimestreIds->isEmpty()) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'No hay cuatrimestres disponibles.',
                'position' => 'top-end',
            ]);
            return;
        }

        $boletas = app(BoletaService::class);
        $alumnos = $boletas->alumnosActivos(
            (int) $this->licenciaturaId,
            (int) $this->modalidadId,
            (int) $this->generacionId
        )->whereIn('id', $seleccionados)->values();

        $analisis = $boletas->analizarAlumnos(
            $alumnos,
            (int) $this->licenciaturaId,
            (int) $this->modalidadId,
            (int) $this->generacionId,
            $cuatrimestreIds
        );

        $encolados = 0;
        $sinCorreo = 0;
        $sinBoletas = 0;

        foreach ($alumnos as $alumno) {
            $correo = $alumno->user?->email;
            if (!$correo) {
                $sinCorreo++;
                continue;
            }

            $periodosValidos = $cuatrimestreIds->filter(function ($cuatriId) use ($analisis, $alumno) {
                $estado = $analisis['alumnos'][$alumno->id]['periodos'][$cuatriId]['codigo'] ?? 'sin_calificaciones';

                if ($estado === 'sin_calificaciones') {
                    return false;
                }

                if ($estado === 'incompleta' && !$this->incluirIncompletas) {
                    return false;
                }

                return true;
            })->values();

            if ($periodosValidos->isEmpty()) {
                $sinBoletas++;
                continue;
            }

            Mail::to($correo)->queue(new BoletasAlumnoMail(
                (int) $alumno->id,
                (int) $this->licenciaturaId,
                (int) $this->modalidadId,
                (int) $this->generacionId,
                $periodosValidos->all(),
                $this->incluirIncompletas
            ));

            $encolados++;
        }

        $this->dispatch('swal', [
            'icon' => $encolados > 0 ? 'success' : 'warning',
            'title' => $encolados > 0
                ? "{$encolados} correo(s) encolado(s)."
                : 'No se encolaron correos.',
            'text' => "Sin correo: {$sinCorreo}. Sin boletas válidas: {$sinBoletas}.",
            'position' => 'top-end',
        ]);
    }

    public function restablecer(): void
    {
        $boletas = app(BoletaService::class);
        $this->reset([
            'licenciaturaId',
            'modalidadId',
            'generacionId',
            'search',
            'seleccionados',
        ]);

        $this->cuatrimestreId = 'todos';
        $this->modo = 'generacion';
        $this->filtroEstado = 'todos';
        $this->formato = 'consolidado';
        $this->incluirIncompletas = false;
        $this->modalidades = [];
        $this->generaciones = [];
        $this->cuatrimestres = [];
        $this->alumnos = [];
        $this->licenciaturas = $boletas->licenciaturasConAlumnos()->toArray();
        $this->resetAnalisis();
    }

    private function cargarModalidades(BoletaService $boletas): void
    {
        if (!$this->licenciaturaId) {
            return;
        }

        $this->modalidades = $boletas->modalidadesConAlumnos($this->licenciaturaId)->toArray();
    }

    private function cargarGeneraciones(BoletaService $boletas): void
    {
        if (!$this->licenciaturaId || !$this->modalidadId) {
            return;
        }

        $this->generaciones = $boletas
            ->generacionesConAlumnos($this->licenciaturaId, $this->modalidadId)
            ->toArray();
    }

    private function cargarCuatrimestres(BoletaService $boletas): void
    {
        if (!$this->licenciaturaId || !$this->modalidadId || !$this->generacionId) {
            return;
        }

        $this->cuatrimestres = $boletas
            ->cuatrimestresDisponibles($this->licenciaturaId, $this->modalidadId, $this->generacionId)
            ->map(fn ($periodo) => [
                'id' => $periodo->id,
                'cuatrimestre_id' => (int) $periodo->cuatrimestre_id,
                'nombre' => $periodo->cuatrimestre?->nombre_cuatrimestre
                    ?? (($periodo->cuatrimestre?->cuatrimestre ?? $periodo->cuatrimestre_id) . '° Cuatrimestre'),
                'numero' => $periodo->cuatrimestre?->cuatrimestre ?? $periodo->cuatrimestre_id,
                'ciclo_escolar' => $periodo->ciclo_escolar,
                'periodo' => $periodo->mes?->meses_corto,
                'etiqueta_academica' => app(\App\Services\AcademicPeriodResolver::class)->label($periodo),
            ])
            ->values()
            ->all();
    }

    private function cargarAlumnos(BoletaService $boletas): void
    {
        if (!$this->licenciaturaId || !$this->modalidadId || !$this->generacionId) {
            return;
        }

        $this->alumnos = $boletas
            ->alumnosActivos($this->licenciaturaId, $this->modalidadId, $this->generacionId)
            ->map(fn ($alumno) => [
                'id' => (int) $alumno->id,
                'nombre' => $alumno->nombre,
                'apellido_paterno' => $alumno->apellido_paterno,
                'apellido_materno' => $alumno->apellido_materno,
                'nombre_completo' => trim("{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombre}"),
                'matricula' => $alumno->matricula,
                'CURP' => $alumno->CURP,
                'email' => $alumno->user?->email,
            ])
            ->values()
            ->all();
    }

    private function recalcularAnalisis(BoletaService $boletas): void
    {
        if (!$this->licenciaturaId || !$this->modalidadId || !$this->generacionId || empty($this->alumnos)) {
            $this->resetAnalisis();
            return;
        }

        $ids = $this->cuatrimestreIdsActuales();
        if ($ids->isEmpty()) {
            $this->resetAnalisis();
            return;
        }

        $modelos = $boletas->alumnosActivos(
            $this->licenciaturaId,
            $this->modalidadId,
            $this->generacionId
        );

        $this->analisis = $boletas
            ->analizarAlumnos($modelos, $this->licenciaturaId, $this->modalidadId, $this->generacionId, $ids);
    }

    private function cuatrimestreIdsActuales(): Collection
    {
        if ($this->cuatrimestreId === 'todos') {
            return collect($this->cuatrimestres)
                ->pluck('cuatrimestre_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
        }

        return ctype_digit((string) $this->cuatrimestreId)
            ? collect([(int) $this->cuatrimestreId])
            : collect();
    }

    private function alumnosVisibles(): Collection
    {
        $search = mb_strtolower(trim($this->search));
        $seleccionados = collect($this->seleccionados)->map(fn ($id) => (int) $id);

        return collect($this->alumnos)
            ->filter(function (array $alumno) use ($search, $seleccionados) {
                if ($search !== '') {
                    $haystack = mb_strtolower(implode(' ', [
                        $alumno['nombre_completo'],
                        $alumno['matricula'],
                        $alumno['CURP'],
                    ]));

                    if (!str_contains($haystack, $search)) {
                        return false;
                    }
                }

                $estado = $this->analisis['alumnos'][$alumno['id']]['estado'] ?? 'sin_calificaciones';

                return match ($this->filtroEstado) {
                    'seleccionados' => $seleccionados->contains((int) $alumno['id']),
                    'pendientes' => !$seleccionados->contains((int) $alumno['id']),
                    'completas' => $estado === 'completa',
                    'incompletas' => $estado === 'incompleta',
                    'sin_calificaciones' => $estado === 'sin_calificaciones',
                    default => true,
                };
            })
            ->values();
    }

    private function resetAnalisis(): void
    {
        $this->analisis = [
            'alumnos' => [],
            'resumen' => [
                'alumnos' => 0,
                'completas' => 0,
                'incompletas' => 0,
                'sin_calificaciones' => 0,
                'boletas_posibles' => 0,
                'boletas_completas' => 0,
                'boletas_incompletas' => 0,
                'boletas_sin_calificaciones' => 0,
            ],
        ];
    }

    public function render()
    {
        $visibles = $this->alumnosVisibles();
        $seleccionados = collect($this->seleccionados)->map(fn ($id) => (int) $id)->unique()->values();
        $seleccionadosValidos = collect($this->alumnos)->pluck('id')->map(fn ($id) => (int) $id)->intersect($seleccionados)->values();

        return view('livewire.admin.documentacion.boletas-masivas', [
            'alumnosVisibles' => $visibles,
            'seleccionadosValidos' => $seleccionadosValidos,
            'resumen' => $this->analisis['resumen'],
        ]);
    }
}
