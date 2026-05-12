<?php

namespace App\Livewire;

use App\Exports\ReprobadosExport;
use App\Models\Calificacion;
use App\Models\Dashboard;
use App\Models\Inscripcion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Header extends Component
{
    public $dashboard;

    // Campanita
    public bool $open = false;

    // Conteos principales
    public int $total = 0;
    public int $conMatricula = 0;
    public int $sinMatricula = 0;
    public int $porcConMatricula = 0;
    public int $porcSinMatricula = 0;

    // Conteo de alumnos con calificación baja
    public int $bajos = 0;
    public int $porcBajos = 0;

    // Modal
    public bool $modalOpen = false;
    public string $modalTipo = 'con'; // con | sin | bajos
    public int $modalLimit = 20;

    // Buscador
    public string $search = '';

    public function mount(): void
    {
        $this->modalOpen = false;
        $this->cargarDashboard();
    }

    #[On('refreshHeader')]
    public function refreshHeader(): void
    {
        $this->cargarDashboard();
    }

    private function cargarDashboard(): void
    {
        $this->dashboard = Dashboard::latest('id')->first();
    }

    public function openModal(string $tipo = 'con'): void
    {
        $this->modalTipo = in_array($tipo, ['con', 'sin', 'bajos'], true) ? $tipo : 'con';
        $this->modalLimit = 20;
        $this->search = '';
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
    }

    public function loadMore(): void
    {
        $this->modalLimit += 20;
    }

    public function updatedSearch(): void
    {
        $this->modalLimit = 20;
    }

    private function tieneColumnaGeneracion(string $columna): bool
    {
        return Schema::hasColumn('generaciones', $columna);
    }

    private function aplicarGeneracionActiva(Builder $query): Builder
    {
        return $query->whereHas('generacion', function ($generacion) {
            if ($this->tieneColumnaGeneracion('activa')) {
                $generacion->where('activa', 'true');
            } elseif ($this->tieneColumnaGeneracion('status')) {
                $generacion->where('status', 'true');
            }
        });
    }

    private function aplicarGeneracionActivaAlumno(Builder $query): Builder
    {
        return $query->whereHas('alumno.generacion', function ($generacion) {
            if ($this->tieneColumnaGeneracion('activa')) {
                $generacion->where('activa', 'true');
            } elseif ($this->tieneColumnaGeneracion('status')) {
                $generacion->where('status', 'true');
            }
        });
    }

    /**
     * Con matrícula:
     * Solo se consideran válidas las matrículas que tienen números.
     */
    private function tieneMatriculaClosure(): \Closure
    {
        return function ($query) {
            $query->whereNotNull('matricula')
                ->whereRaw("TRIM(COALESCE(matricula, '')) <> ''")
                ->whereRaw("TRIM(matricula) REGEXP '^[0-9]+$'");
        };
    }

    /**
     * Sin matrícula:
     * Incluye matrícula vacía, nula o con letras/caracteres.
     */
    private function sinMatriculaClosure(): \Closure
    {
        return function ($query) {
            $query->where(function ($where) {
                $where->whereNull('matricula')
                    ->orWhereRaw("TRIM(COALESCE(matricula, '')) = ''")
                    ->orWhereRaw("TRIM(matricula) NOT REGEXP '^[0-9]+$'");
            });
        };
    }

    private function gradeColumn(): ?string
    {
        $candidatas = [
            'calificacion_final',
            'promedio_final',
            'promedio',
            'final',
            'calificacion',
        ];

        foreach ($candidatas as $columna) {
            if (Schema::hasColumn('calificaciones', $columna)) {
                return $columna;
            }
        }

        return null;
    }

    private function bajosClosure(string $columna): \Closure
    {
        return function ($query) use ($columna) {
            $query->where(function ($where) use ($columna) {
                $where->whereIn(DB::raw("UPPER($columna)"), ['NP', 'N/P', 'N.P.', 'NA'])
                    ->orWhere(function ($numero) use ($columna) {
                        $numero->whereRaw("$columna REGEXP '^[0-9]+(\\.[0-9]+)?$'")
                            ->whereRaw("CAST($columna AS DECIMAL(5,2)) <= 6");
                    });
            });
        };
    }

    public function getAlumnosProperty()
    {
        $term = trim($this->search ?? '');
        $like = '%' . preg_replace('/\s+/', '%', $term) . '%';

        $norm = mb_strtolower($term, 'UTF-8');
        $hasForaneo = str_contains($norm, 'foráneo') || str_contains($norm, 'foraneo');
        $hasLocal = str_contains($norm, 'local');

        if ($this->modalTipo === 'bajos' && !$this->gradeColumn()) {
            return collect();
        }

        $columnaCalificacion = $this->gradeColumn();

        $query = Inscripcion::query()
            ->select([
                'id',
                'matricula',
                'nombre',
                'apellido_paterno',
                'apellido_materno',
                'licenciatura_id',
                'modalidad_id',
                'cuatrimestre_id',
                'generacion_id',
                'foraneo',
                'created_at',
            ])
            ->with([
                'licenciatura',
                'modalidad',
                'cuatrimestre',
                'generacion',
                'calificaciones.asignacionMateria.materia',
                'calificaciones.asignacionMateria.cuatrimestre',
            ]);

        $this->aplicarGeneracionActiva($query);

        $query
            ->when($this->modalTipo === 'con', $this->tieneMatriculaClosure())
            ->when($this->modalTipo === 'sin', $this->sinMatriculaClosure())
            ->when($this->modalTipo === 'bajos' && $columnaCalificacion, function ($query) use ($columnaCalificacion) {
                $query->whereHas('calificaciones', $this->bajosClosure($columnaCalificacion));
            })
            ->when($term !== '' && $hasForaneo && !$hasLocal, function ($query) {
                $query->whereIn('foraneo', [1, '1', true, 'true']);
            })
            ->when($term !== '' && $hasLocal && !$hasForaneo, function ($query) {
                $query->whereIn('foraneo', [0, '0', false, 'false']);
            })
            ->when($term !== '', function ($query) use ($like, $hasForaneo, $hasLocal) {
                $query->where(function ($where) use ($like, $hasForaneo, $hasLocal) {
                    $where->where('matricula', 'like', $like)
                        ->orWhere('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhereHas('licenciatura', function ($relacion) use ($like) {
                            $relacion->where('nombre', 'like', $like);
                        })
                        ->orWhereHas('modalidad', function ($relacion) use ($like) {
                            $relacion->where('nombre', 'like', $like);
                        })
                        ->orWhereHas('generacion', function ($relacion) use ($like) {
                            $relacion->where('generacion', 'like', $like);
                        })
                        ->orWhereHas('cuatrimestre', function ($relacion) use ($like) {
                            $relacion->where('cuatrimestre', 'like', $like);
                        });

                    if ($hasForaneo) {
                        $where->orWhereIn('foraneo', [1, '1', true, 'true']);
                    }

                    if ($hasLocal) {
                        $where->orWhereIn('foraneo', [0, '0', false, 'false']);
                    }
                });
            })
            ->latest('id');

        return $query->limit($this->modalLimit)->get();
    }

    public function exportarReprobados()
    {
        $term = trim($this->search ?? '');
        $like = '%' . preg_replace('/\s+/', '%', $term) . '%';

        $norm = mb_strtolower($term, 'UTF-8');
        $hasForaneo = str_contains($norm, 'foráneo') || str_contains($norm, 'foraneo');
        $hasLocal = str_contains($norm, 'local');

        $codigosNP = ['NP', 'N/P', 'N.P.', 'NA'];

        $query = Calificacion::query()
            ->with([
                'alumno.licenciatura',
                'alumno.modalidad',
                'alumno.cuatrimestre',
                'alumno.generacion',
                'asignacionMateria.materia',
                'asignacionMateria.cuatrimestre',
            ]);

        $this->aplicarGeneracionActivaAlumno($query);

        $query
            ->where(function ($where) use ($codigosNP) {
                $where->whereIn(DB::raw('UPPER(calificaciones.calificacion)'), $codigosNP)
                    ->orWhere(function ($numero) {
                        $numero->whereRaw("calificaciones.calificacion REGEXP '^[0-9]+(\\.[0-9]+)?$'")
                            ->whereRaw('CAST(calificaciones.calificacion AS DECIMAL(5,2)) <= 6');
                    });
            })
            ->when($term !== '', function ($query) use ($like, $hasForaneo, $hasLocal) {
                $query->whereHas('alumno', function ($alumno) use ($like, $hasForaneo, $hasLocal) {
                    $alumno->where('matricula', 'like', $like)
                        ->orWhere('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhereHas('licenciatura', function ($relacion) use ($like) {
                            $relacion->where('nombre', 'like', $like);
                        })
                        ->orWhereHas('modalidad', function ($relacion) use ($like) {
                            $relacion->where('nombre', 'like', $like);
                        })
                        ->orWhereHas('generacion', function ($relacion) use ($like) {
                            $relacion->where('generacion', 'like', $like);
                        })
                        ->orWhereHas('cuatrimestre', function ($relacion) use ($like) {
                            $relacion->where('cuatrimestre', 'like', $like);
                        });

                    if ($hasForaneo) {
                        $alumno->orWhereIn('foraneo', [1, '1', true, 'true']);
                    }

                    if ($hasLocal) {
                        $alumno->orWhereIn('foraneo', [0, '0', false, 'false']);
                    }
                });
            })
            ->latest('id');

        $rows = $query->get();

        return Excel::download(new ReprobadosExport($rows), 'reprobados_filtrados.xlsx');
    }

    public function render()
    {
        $base = Inscripcion::query();

        $this->aplicarGeneracionActiva($base);

        $stats = (clone $base)
            ->selectRaw("
                COUNT(*) AS total,

                SUM(
                    CASE
                        WHEN matricula IS NOT NULL
                        AND TRIM(COALESCE(matricula, '')) <> ''
                        AND TRIM(matricula) REGEXP '^[0-9]+$'
                        THEN 1
                        ELSE 0
                    END
                ) AS con_matricula,

                SUM(
                    CASE
                        WHEN matricula IS NULL
                        OR TRIM(COALESCE(matricula, '')) = ''
                        OR TRIM(matricula) NOT REGEXP '^[0-9]+$'
                        THEN 1
                        ELSE 0
                    END
                ) AS sin_matricula
            ")
            ->first();

        $this->total = (int) ($stats->total ?? 0);
        $this->conMatricula = (int) ($stats->con_matricula ?? 0);
        $this->sinMatricula = (int) ($stats->sin_matricula ?? 0);

        $this->porcConMatricula = $this->total > 0
            ? (int) round(($this->conMatricula / $this->total) * 100)
            : 0;

        $this->porcSinMatricula = $this->total > 0
            ? (int) round(($this->sinMatricula / $this->total) * 100)
            : 0;

        $columnaCalificacion = $this->gradeColumn();

        if ($columnaCalificacion) {
            $this->bajos = (clone $base)
                ->whereHas('calificaciones', $this->bajosClosure($columnaCalificacion))
                ->count();
        } else {
            $this->bajos = 0;
        }

        $this->porcBajos = $this->total > 0
            ? (int) round(($this->bajos / $this->total) * 100)
            : 0;

        return view('livewire.header');
    }

    public function modalidadChip(?string $nombre): string
    {
        $n = strtolower(trim($nombre ?? ''));

        return match (true) {
            str_contains($n, 'escolar') =>
            'bg-sky-50 text-sky-700 ring-1 ring-sky-200 dark:bg-sky-900/20 dark:text-sky-200 dark:ring-sky-800/60',

            str_contains($n, 'sabat') =>
            'bg-rose-50 text-rose-700 ring-1 ring-rose-200 dark:bg-rose-900/20 dark:text-rose-200 dark:ring-rose-800/60',

            str_contains($n, 'mixt') =>
            'bg-amber-50 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-800 dark:ring-amber-800/60',

            str_contains($n, 'línea') ||
                str_contains($n, 'linea') ||
                str_contains($n, 'online') ||
                str_contains($n, 'virtual') =>
            'bg-purple-50 text-purple-700 ring-1 ring-purple-200 dark:bg-purple-900/20 dark:text-purple-200 dark:ring-purple-800/60',

            default =>
            'bg-neutral-100 text-neutral-700 ring-1 ring-neutral-200 dark:bg-neutral-700/60 dark:text-neutral-200 dark:ring-neutral-600/60',
        };
    }
}
