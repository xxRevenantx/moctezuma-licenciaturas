<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Inscripcion;

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

    // --- Modal de alumnos ---
    public bool $modalOpen = false;       // debe iniciar cerrado
    public string $modalTipo = 'con';     // 'con' | 'sin'
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
        $this->modalTipo = in_array($tipo, ['con', 'sin']) ? $tipo : 'con';
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
        // Al cambiar búsqueda, reinicia el límite
        $this->modalLimit = 20;
    }

    /** Lista de alumnos para el modal (solo generaciones activas) */
   public function getAlumnosProperty()
{
    $condSqlBetween = "CAST(SUBSTRING(matricula,1,2) AS UNSIGNED) BETWEEN ? AND ?";
    $bindingsRange  = [$this->prefijoMin, $this->prefijoMax];

    $term = trim($this->search ?? '');
    // Normaliza espacios para búsquedas parciales
    $like = '%'.preg_replace('/\s+/', '%', $term).'%';

    // Detecta intención: foráneo / local (con y sin acento)
    $norm = mb_strtolower($term, 'UTF-8');
    $hasForaneo = str_contains($norm, 'foráneo') || str_contains($norm, 'foraneo');
    $hasLocal   = str_contains($norm, 'local');

    $q = Inscripcion::query()
        ->select([
            'id','matricula','nombre','apellido_paterno','apellido_materno',
            'licenciatura_id','modalidad_id','cuatrimestre_id','generacion_id',
            'foraneo','created_at'
        ])
        ->with(['licenciatura','modalidad','cuatrimestre','generacion'])
        // Solo generaciones activas
        ->whereHas('generacion', fn($g) => $g->where('activa', 'true'))
        // Grupo (con prefijo 20–99 o sin)
        ->when(
            $this->modalTipo === 'con',
            fn ($qq) => $qq->whereRaw($condSqlBetween, $bindingsRange),
            fn ($qq) => $qq->where(function ($w) use ($condSqlBetween, $bindingsRange) {
                $w->whereNull('matricula')
                  ->orWhereRaw("NOT ($condSqlBetween)", $bindingsRange);
            })
        )
        // Filtro exclusivo por foráneo/local cuando aplica SOLO uno de los dos términos
        ->when($term !== '' && $hasForaneo && !$hasLocal,
            fn($qq) => $qq->whereIn('foraneo', [1, '1', true, 'true'])
        )
        ->when($term !== '' && $hasLocal && !$hasForaneo,
            fn($qq) => $qq->whereIn('foraneo', [0, '0', false, 'false'])
        )
        // Búsqueda textual + match por foráneo/local dentro del OR general
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

                // Si el término incluye estas palabras, también lo consideramos en el OR
                if ($hasForaneo) {
                    $w->orWhereIn('foraneo', [1, '1', true, 'true']);
                }
                if ($hasLocal) {
                    $w->orWhereIn('foraneo', [0, '0', false, 'false']);
                }
            });
        })
        ->latest();

    return $q->limit($this->modalLimit)->get();
}


    public function render()
    {
        // Base: solo generaciones activas
        $base = Inscripcion::query()
            ->whereHas('generacion', fn($g) => $g->where('activa', 'true'));

        // Conteos sobre la base filtrada
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
