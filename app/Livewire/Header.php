<?php

namespace App\Livewire;

use App\Exports\ReprobadosExport;
use App\Models\Calificacion;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class Header extends Component
{
    public $dashboard;

    // --- Campanita (conteos 20–99) ---
    public bool $open = false;
    public int $total = 0;
    public int $conPrefijo = 0;   // comienzan con 20..99
    public int $sinPrefijo = 0;   // no comienzan con 20..99 (incluye NULL)
    public int $porcConPrefijo = 0;
    public int $porcSinPrefijo = 0;
    public int $prefijoMin = 20;
    public int $prefijoMax = 99;

    // --- NUEVO: conteo alumnos con ≤6 o NP ---
    public int $bajos = 0;
    public int $porcBajos = 0;

    // --- Modal de alumnos ---
    public bool $modalOpen = false;       // debe iniciar cerrado
    public string $modalTipo = 'con';     // 'con' | 'sin' | 'bajos'
    public int $modalLimit = 20;          // filas a mostrar en el modal

    // --- Buscador ---
    public string $search = '';

    #[On('refreshHeader')]
    public function mount()
    {
        $this->modalOpen = false; // cerrado al cargar
        $this->dashboard = \App\Models\Dashboard::latest('id')->first();
    }

    public function openModal(string $tipo = 'con'): void
    {
        $this->modalTipo = in_array($tipo, ['con', 'sin', 'bajos']) ? $tipo : 'con';
        $this->modalLimit = 20;
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

    /**
     * Detecta la columna de nota final disponible en la tabla calificaciones.
     * Ajusta este arreglo si el nombre de tu columna es diferente.
     */
    private function gradeColumn(): ?string
    {
        $candidatas = ['calificacion_final','promedio_final','promedio','final','calificacion'];
        foreach ($candidatas as $col) {
            if (Schema::hasColumn('calificaciones', $col)) {
                return $col;
            }
        }
        return null;
    }

    /**
     * Clausura para filtrar calificaciones ≤ 6 o códigos NP/N.P./N-P/NA (ajústalo a tus códigos).
     */
    private function bajosClosure(string $col): \Closure
    {
        return function ($q) use ($col) {
            $q->where(function ($w) use ($col) {
                // Códigos tipo NP/NA
                $w->whereIn(DB::raw("UPPER($col)"), ['NP','N/P','N.P.','NA'])
                  // Numéricas válidas y ≤ 6
                  ->orWhere(function ($num) use ($col) {
                      $num->whereRaw("$col REGEXP '^[0-9]+(\\.[0-9]+)?$'")
                          ->whereRaw("CAST($col AS DECIMAL(5,2)) <= 6");
                  });
            });
        };
    }

    /** Lista de alumnos para el modal (solo generaciones activas) */
    public function getAlumnosProperty()
    {
        $condSqlBetween = "CAST(SUBSTRING(matricula,1,2) AS UNSIGNED) BETWEEN ? AND ?";
        $bindingsRange  = [$this->prefijoMin, $this->prefijoMax];

        $term = trim($this->search ?? '');
        $like = '%'.preg_replace('/\s+/', '%', $term).'%';

        $norm = mb_strtolower($term, 'UTF-8');
        $hasForaneo = str_contains($norm, 'foráneo') || str_contains($norm, 'foraneo');
        $hasLocal   = str_contains($norm, 'local');

        // Si el modal es "bajos" pero no detectamos columna, regresamos vacío
        if ($this->modalTipo === 'bajos' && !$this->gradeColumn()) {
            return collect();
        }
        $col = $this->gradeColumn();

        $q = Inscripcion::query()
            ->select([
                'id','matricula','nombre','apellido_paterno','apellido_materno',
                'licenciatura_id','modalidad_id','cuatrimestre_id','generacion_id',
                'foraneo','created_at'
            ])
          ->with(['licenciatura','modalidad','cuatrimestre','generacion','calificaciones.asignacionMateria.materia'])

            // Grupo (con/sin prefijo; para "bajos" no importa el prefijo)
            ->when(
                $this->modalTipo === 'con',
                fn ($qq) => $qq->whereRaw($condSqlBetween, $bindingsRange),
                fn ($qq) => $this->modalTipo === 'sin'
                    ? $qq->where(function ($w) use ($condSqlBetween, $bindingsRange) {
                        $w->whereNull('matricula')
                          ->orWhereRaw("NOT ($condSqlBetween)", $bindingsRange);
                      })
                    : $qq // 'bajos' -> no filtra por prefijo
            )
            // Filtrado por "bajos": tener al menos una calificación ≤ 6 o NP
            ->when($this->modalTipo === 'bajos', fn($qq) => $qq->whereHas('calificaciones', $this->bajosClosure($col)))
            // Filtro exclusivo por foráneo/local cuando aplica SOLO uno de los dos términos
            ->when($term !== '' && $hasForaneo && !$hasLocal,
                fn($qq) => $qq->whereIn('foraneo', [1, '1', true, 'true'])
            )
            ->when($term !== '' && $hasLocal && !$hasForaneo,
                fn($qq) => $qq->whereIn('foraneo', [0, '0', false, 'false'])
            )
            // Búsqueda textual
            ->when($term !== '', function ($qq) use ($like, $hasForaneo, $hasLocal) {
                $qq->where(function ($w) use ($like, $hasForaneo, $hasLocal) {
                    $w->where('matricula', 'like', $like)
                      ->orWhere('nombre', 'like', $like)
                      ->orWhere('apellido_paterno', 'like', $like)
                      ->orWhere('apellido_materno', 'like', $like)
                      ->orWhereHas('licenciatura',  fn($r) => $r->where('nombre', 'like', $like))
                      ->orWhereHas('modalidad',    fn($r) => $r->where('nombre', 'like', $like))
                      ->orWhereHas('generacion',   fn($r) => $r->where('generacion', 'like', $like))
                      ->orWhereHas('cuatrimestre', fn($r) => $r->where('cuatrimestre', 'like', $like));


                    if ($hasForaneo) $w->orWhereIn('foraneo', [1, '1', true, 'true']);
                    if ($hasLocal)   $w->orWhereIn('foraneo', [0, '0', false, 'false']);
                });
            })
            ->latest();

        return $q->limit($this->modalLimit)->get();
    }

     public function exportarReprobados()
    {
        $condSqlBetween = "CAST(SUBSTRING(matricula,1,2) AS UNSIGNED) BETWEEN ? AND ?";
        $bindingsRange  = [$this->prefijoMin, $this->prefijoMax];

        $term = trim($this->search ?? '');
        $like = '%'.preg_replace('/\s+/', '%', $term).'%';

        $norm = mb_strtolower($term, 'UTF-8');
        $hasForaneo = str_contains($norm, 'foráneo') || str_contains($norm, 'foraneo');
        $hasLocal   = str_contains($norm, 'local');

        // Códigos tratados como NP/NA
        $codigosNP = ['NP','N/P','N.P.','NA'];

        // Construye el mismo filtro que usas para el modal,
        // pero partiendo de Calificacion y trayendo la materia.
        $q = Calificacion::query()
            ->with([
                'alumno.licenciatura','alumno.modalidad','alumno.cuatrimestre','alumno.generacion',
                'asignacionMateria.materia',
            ])
            // Solo generaciones activas
            ->whereHas('alumno.generacion', fn($g) => $g->where('activa', 'true'))
            // Grupo (con/sin prefijo; si estás en "bajos" no filtramos prefijo)
            ->when(
                $this->modalTipo === 'con',
                fn($qq) => $qq->whereHas('alumno', fn($a) => $a->whereRaw($condSqlBetween, $bindingsRange)),
                fn($qq) => $this->modalTipo === 'sin'
                    ? $qq->whereHas('alumno', function ($a) use ($condSqlBetween, $bindingsRange) {
                        $a->whereNull('matricula')
                          ->orWhereRaw("NOT ($condSqlBetween)", $bindingsRange);
                      })
                    : $qq // 'bajos' -> sin filtro por prefijo
            )
            // Reprobados: ≤6 o código NP/NA
            ->where(function ($w) use ($codigosNP) {
                $w->whereIn(DB::raw('UPPER(calificaciones.calificacion)'), $codigosNP)
                  ->orWhere(function ($num) {
                      $num->whereRaw("calificaciones.calificacion REGEXP '^[0-9]+(\\.[0-9]+)?$'")
                          ->whereRaw('CAST(calificaciones.calificacion AS DECIMAL(5,2)) <= 6');
                  });
            })
            // Búsqueda textual (sobre el alumno y relaciones)
            ->when($term !== '', function ($qq) use ($like, $hasForaneo, $hasLocal) {
                $qq->whereHas('alumno', function ($aq) use ($like, $hasForaneo, $hasLocal) {
                    $aq->where('matricula', 'like', $like)
                       ->orWhere('nombre', 'like', $like)
                       ->orWhere('apellido_paterno', 'like', $like)
                       ->orWhere('apellido_materno', 'like', $like)
                       ->orWhereHas('licenciatura',  fn($r) => $r->where('nombre', 'like', $like))
                       ->orWhereHas('modalidad',    fn($r) => $r->where('nombre', 'like', $like))
                       ->orWhereHas('generacion',   fn($r) => $r->where('generacion', 'like', $like))
                       ->orWhereHas('cuatrimestre', fn($r) => $r->where('cuatrimestre', 'like', $like));

                    if ($hasForaneo) $aq->orWhereIn('foraneo', [1, '1', true, 'true']);
                    if ($hasLocal)   $aq->orWhereIn('foraneo', [0, '0', false, 'false']);
                });
            })
            ->latest('id');

        // Pasamos la colección filtrada al export
        $rows = $q->get();

        return Excel::download(new ReprobadosExport($rows), 'reprobados_filtrados.xlsx');
    }

    public function render()
    {
        // Base: solo generaciones activas
        $base = Inscripcion::query()
            ->whereHas('generacion', fn($g) => $g->where('activa', 'true'));

        // Conteos por prefijo
        $stats = (clone $base)->selectRaw("
            COUNT(*) AS total,
            SUM(CASE WHEN CAST(SUBSTRING(matricula,1,2) AS UNSIGNED) BETWEEN ? AND ? THEN 1 ELSE 0 END) AS conPrefijo,
            SUM(CASE WHEN matricula IS NULL OR CAST(SUBSTRING(matricula,1,2) AS UNSIGNED) NOT BETWEEN ? AND ? THEN 1 ELSE 0 END) AS sinPrefijo
        ", [$this->prefijoMin, $this->prefijoMax, $this->prefijoMin, $this->prefijoMax])->first();

        $this->total          = (int) ($stats->total ?? 0);
        $this->conPrefijo     = (int) ($stats->conPrefijo ?? 0);
        $this->sinPrefijo     = (int) ($stats->sinPrefijo ?? 0);
        $this->porcConPrefijo = $this->total > 0 ? (int) round(($this->conPrefijo / $this->total) * 100) : 0;
        $this->porcSinPrefijo = $this->total > 0 ? (int) round(($this->sinPrefijo / $this->total) * 100) : 0;

        // Conteo de alumnos con ≤6 o NP
        $col = $this->gradeColumn();
        if ($col) {
            $this->bajos = (clone $base)
                ->whereHas('calificaciones', $this->bajosClosure($col))
                ->count();
        } else {
            $this->bajos = 0; // no se detectó columna de calificación
        }
        $this->porcBajos = $this->total > 0 ? (int) round(($this->bajos / $this->total) * 100) : 0;

        return view('livewire.header');
    }

    /** Clases de color para el chip de modalidad según su nombre */
    public function modalidadChip(?string $nombre): string
    {
        $n = strtolower(trim($nombre ?? ''));
        return match (true) {
            str_contains($n, 'escolar')   => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200 dark:bg-sky-900/20 dark:text-sky-200 dark:ring-sky-800/60',
            str_contains($n, 'sabat')     => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200 dark:bg-rose-900/20 dark:text-rose-200 dark:ring-rose-800/60',
            str_contains($n, 'mixt')      => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-200 dark:ring-amber-800/60',
            str_contains($n, 'línea') || str_contains($n, 'linea') || str_contains($n, 'online') || str_contains($n, 'virtual')
                                         => 'bg-purple-50 text-purple-700 ring-1 ring-purple-200 dark:bg-purple-900/20 dark:text-purple-200 dark:ring-purple-800/60',
            default                       => 'bg-neutral-100 text-neutral-700 ring-1 ring-neutral-200 dark:bg-neutral-700/60 dark:text-neutral-200 dark:ring-neutral-600/60',
        };
    }
}
