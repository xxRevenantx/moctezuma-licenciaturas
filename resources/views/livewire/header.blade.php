<div class="w-full">
    <style>[x-cloak]{display:none!important}</style>

    <div class="relative mb-4 flex w-full flex-wrap items-center justify-between gap-4 overflow-visible rounded-2xl border border-neutral-200 bg-white/90 p-4 shadow-lg dark:border-neutral-700 dark:bg-neutral-800/90 sm:p-5">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#006492] via-sky-500 to-[#88AC2E]"></div>

        <div class="flex w-full items-center justify-center gap-2 text-neutral-700 dark:text-neutral-100 sm:w-auto lg:justify-start">
            <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-[#006492] dark:bg-sky-900/40 dark:text-sky-300">
                <flux:icon.calendar />
            </div>
            <span class="font-medium">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</span>
        </div>

        <div class="mt-2 flex w-full flex-col items-center gap-3 sm:mt-0 sm:w-auto lg:flex-row">
            <div x-data="{ open: @entangle('open') }" x-cloak class="relative" wire:poll.30s="refreshHeader">
                <button type="button" @click="open = !open"
                    class="group relative rounded-xl p-2 transition hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-[#006492] dark:hover:bg-neutral-700"
                    aria-label="Control de matrículas">
                    <svg class="h-6 w-6 text-neutral-700 transition group-hover:scale-105 dark:text-neutral-200" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22m6-6v-5a6 6 0 0 0-4-5.65V4a2 2 0 0 0-4 0v.35A6 6 0 0 0 6 11v5l-2 2v1h16v-1z" />
                    </svg>
                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[1.15rem] items-center justify-center rounded-full bg-[#006492] px-1 text-[11px] font-semibold text-white shadow ring-2 ring-white dark:ring-neutral-800">
                        {{ $total }}
                    </span>
                </button>

                <div x-show="open" x-transition @click.outside="open = false" @keydown.escape.window="open = false"
                    class="absolute right-0 z-[10001] mt-2 w-[min(92vw,25rem)] overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-neutral-200 dark:bg-neutral-800 dark:ring-neutral-700">
                    <div class="flex items-center justify-between border-b border-neutral-100 p-4 dark:border-neutral-700">
                        <div>
                            <h4 class="font-semibold text-neutral-900 dark:text-neutral-100">Control de matrículas</h4>
                            <p class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">Generaciones activas</p>
                        </div>
                        <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-bold text-[#006492] dark:bg-sky-950/50 dark:text-sky-200">{{ $total }}</span>
                    </div>

                    <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        <button type="button" wire:click="openModal('con')" @click="open=false"
                            class="flex w-full items-start gap-3 p-4 text-left transition hover:bg-sky-50 dark:hover:bg-sky-950/20">
                            <span class="mt-1.5 h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                            <span class="flex-1">
                                <span class="block font-medium text-neutral-900 dark:text-neutral-100">Alumnos con matrícula válida</span>
                                <span class="mt-1 block text-sm text-neutral-600 dark:text-neutral-300">{{ $conMatricula }} alumnos · {{ $porcConMatricula }}%</span>
                                <span class="mt-1 block text-[11px] text-neutral-500 dark:text-neutral-400">Formato institucional: cuatro letras y cuatro dígitos.</span>
                            </span>
                        </button>

                        <button type="button" wire:click="openModal('sin')" @click="open=false"
                            class="flex w-full items-start gap-3 p-4 text-left transition hover:bg-amber-50 dark:hover:bg-amber-950/20">
                            <span class="mt-1.5 h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                            <span class="flex-1">
                                <span class="block font-medium text-neutral-900 dark:text-neutral-100">Alumnos sin matrícula válida</span>
                                <span class="mt-1 block text-sm text-neutral-600 dark:text-neutral-300">{{ $sinMatricula }} alumnos · {{ $porcSinMatricula }}%</span>
                                <span class="mt-1 block text-[11px] text-neutral-500 dark:text-neutral-400">{{ $matriculasVacias }} vacías · {{ $matriculasIncorrectas }} con formato incorrecto · {{ $matriculasDuplicadas }} duplicadas</span>
                            </span>
                        </button>

                        <button type="button" wire:click="openModal('bajos')" @click="open=false"
                            class="flex w-full items-start gap-3 p-4 text-left transition hover:bg-red-50 dark:hover:bg-red-950/20">
                            <span class="mt-1.5 h-2.5 w-2.5 rounded-full bg-red-500"></span>
                            <span class="flex-1">
                                <span class="block font-medium text-neutral-900 dark:text-neutral-100">Alumnos con riesgo académico</span>
                                <span class="mt-1 block text-sm text-neutral-600 dark:text-neutral-300">{{ $bajos }} alumnos · {{ $porcBajos }}%</span>
                                <span class="mt-1 block text-[11px] text-neutral-500 dark:text-neutral-400">Calificación menor o igual a 6, NP, N/P, N.P. o NA.</span>
                            </span>
                        </button>

                        <button type="button" wire:click="openModal('todos')" @click="open=false"
                            class="flex w-full items-start gap-3 p-4 text-left transition hover:bg-emerald-50 dark:hover:bg-emerald-950/20">
                            <span class="mt-1.5 h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            <span class="flex-1">
                                <span class="block font-medium text-neutral-900 dark:text-neutral-100">Total de inscripciones</span>
                                <span class="mt-1 block text-sm text-neutral-600 dark:text-neutral-300">{{ $total }} registros con filtros, exportación y acciones.</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="inline-flex items-center gap-2">
                <div class="rounded-xl border border-neutral-200 bg-neutral-50 px-3 py-2 text-sm text-neutral-800 dark:border-neutral-600 dark:bg-neutral-700/40 dark:text-neutral-100">
                    Ciclo escolar
                    <flux:badge color="indigo" class="ml-2">{{ $dashboard->ciclo_escolar ?? '0' }}</flux:badge>
                </div>
                <div class="rounded-xl border border-neutral-200 bg-neutral-50 px-3 py-2 text-sm text-neutral-800 dark:border-neutral-600 dark:bg-neutral-700/40 dark:text-neutral-100">
                    Periodo escolar
                    <flux:badge class="ml-2 uppercase" color="indigo">{{ $dashboard->periodo_escolar ?? '0' }}</flux:badge>
                </div>
            </div>

            @if (auth()->user()->photo)
                <div class="relative hidden h-10 w-10 lg:block">
                    @if (file_exists(storage_path('app/public/profile-photos/' . auth()->user()->photo)))
                        <div class="h-full w-full overflow-hidden rounded-full border-4 border-white shadow ring-1 ring-neutral-200 dark:ring-neutral-700">
                            <img src="{{ asset('storage/profile-photos/' . auth()->user()->photo) }}" alt="Avatar" class="h-full w-full object-cover">
                        </div>
                    @else
                        <flux:avatar circle badge badge:circle badge:color="green" :initials="auth()->user()->initials()" :name="auth()->user()->name" />
                    @endif
                    <span class="absolute bottom-0 right-0 h-4 w-4 rounded-full border-2 border-white bg-green-500 shadow-md dark:border-neutral-800"></span>
                </div>
            @else
                <flux:avatar circle badge badge:circle badge:color="green" class="hidden lg:block" :initials="auth()->user()->initials()" :name="auth()->user()->name" />
            @endif
        </div>
    </div>

    <div class="fixed inset-0 z-[9999] flex items-center justify-center" wire:loading wire:target="openModal" role="status">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px]"></div>
        <div class="relative flex items-center gap-3 rounded-2xl bg-white px-6 py-5 shadow-2xl ring-1 ring-neutral-200 dark:bg-neutral-800 dark:ring-neutral-700">
            <span class="h-6 w-6 animate-spin rounded-full border-2 border-neutral-300 border-t-transparent dark:border-neutral-600"></span>
            <span class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Preparando el reporte…</span>
        </div>
    </div>

    @if ($modalOpen && $alumnos)
        @php
            $pageIds = $alumnos->pluck('id')->map(fn ($id) => (int) $id)->all();
            $pageAllSelected = count($pageIds) > 0 && collect($pageIds)->every(fn ($id) => $this->isSelected($id));
            $chartSignature = md5(json_encode($chartData));
        @endphp

        <div x-data="{ filtersOpen: true, chartOpen: true, exportOpen: false }" x-cloak
            x-init="document.documentElement.classList.add('overflow-hidden'); $cleanup(() => document.documentElement.classList.remove('overflow-hidden'))"
            class="fixed inset-0 z-[10000] flex items-center justify-center p-2 sm:p-4"
            @keydown.escape.window="$wire.closeModal()" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>

            <div class="relative flex h-[96vh] w-full max-w-[1600px] flex-col overflow-hidden rounded-3xl bg-neutral-50 shadow-2xl ring-1 ring-black/10 dark:bg-neutral-900 dark:ring-white/10">
                <header class="flex shrink-0 flex-col gap-3 border-b border-neutral-200 bg-white px-4 py-4 dark:border-neutral-700 dark:bg-neutral-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-extrabold text-neutral-900 dark:text-white sm:text-2xl">
                                @switch($modalTipo)
                                    @case('sin') Alumnos sin matrícula válida @break
                                    @case('bajos') Riesgo académico @break
                                    @case('todos') Total de inscripciones @break
                                    @default Alumnos con matrícula válida
                                @endswitch
                            </h2>
                            <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-[#006492] dark:bg-sky-950/50 dark:text-sky-200">{{ number_format($alumnos->total()) }} resultados</span>
                        </div>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Filtros dependientes, selección, exportaciones completas y acciones individuales.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="filtersOpen = !filtersOpen"
                            class="rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm font-semibold text-neutral-700 transition hover:border-[#006492] hover:text-[#006492] dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-200">
                            Filtros
                        </button>
                        <button type="button" @click="chartOpen = !chartOpen"
                            class="rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm font-semibold text-neutral-700 transition hover:border-[#88AC2E] hover:text-[#5f7e16] dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-200">
                            Gráfica
                        </button>
                        <button type="button" wire:click="closeModal" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-neutral-100 text-xl text-neutral-600 transition hover:bg-red-50 hover:text-red-600 dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-red-950/40">×</button>
                    </div>
                </header>

                <main class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <article class="rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 to-white p-4 dark:border-sky-900/50 dark:from-sky-950/40 dark:to-neutral-800">
                            <p class="text-xs font-bold uppercase tracking-wider text-[#006492] dark:text-sky-300">Resultados filtrados</p>
                            <p class="mt-2 text-3xl font-black text-neutral-900 dark:text-white">{{ number_format($modalStats['total']) }}</p>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ $selectionCount }} seleccionado(s)</p>
                        </article>
                        <article class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-4 dark:border-emerald-900/50 dark:from-emerald-950/40 dark:to-neutral-800">
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Estado escolar</p>
                            <p class="mt-2 text-3xl font-black text-neutral-900 dark:text-white">{{ $modalStats['activos'] }}</p>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Activos · {{ $modalStats['bajas'] }} bajas</p>
                        </article>
                        <article class="rounded-2xl border border-lime-100 bg-gradient-to-br from-lime-50 to-white p-4 dark:border-lime-900/50 dark:from-lime-950/40 dark:to-neutral-800">
                            <p class="text-xs font-bold uppercase tracking-wider text-[#5f7e16] dark:text-lime-300">Procedencia</p>
                            <p class="mt-2 text-3xl font-black text-neutral-900 dark:text-white">{{ $modalStats['locales'] }}</p>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Locales · {{ $modalStats['foraneos'] }} foráneos</p>
                        </article>
                        <article class="rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-4 dark:border-violet-900/50 dark:from-violet-950/40 dark:to-neutral-800">
                            <p class="text-xs font-bold uppercase tracking-wider text-violet-700 dark:text-violet-300">Distribución por sexo</p>
                            <p class="mt-2 text-3xl font-black text-neutral-900 dark:text-white">{{ $modalStats['mujeres'] }}</p>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Mujeres · {{ $modalStats['hombres'] }} hombres</p>
                        </article>
                    </section>

                    @if ($modalTipo === 'sin')
                        <section class="mt-4 flex flex-wrap gap-2 rounded-2xl border border-amber-200 bg-amber-50/70 p-3 dark:border-amber-900/50 dark:bg-amber-950/20">
                            @foreach ([
                                'todos' => ['Todos', $sinCounts['todos']],
                                'vacias' => ['Vacías', $sinCounts['vacias']],
                                'formato' => ['Formato incorrecto', $sinCounts['formato']],
                                'duplicadas' => ['Duplicadas', $sinCounts['duplicadas']],
                            ] as $valor => [$etiqueta, $cantidad])
                                <button type="button" wire:click="$set('sinCategoria', '{{ $valor }}')"
                                    class="rounded-xl px-3 py-2 text-sm font-semibold transition {{ $sinCategoria === $valor ? 'bg-amber-500 text-white shadow' : 'bg-white text-amber-800 ring-1 ring-amber-200 hover:bg-amber-100 dark:bg-neutral-800 dark:text-amber-200 dark:ring-amber-900' }}">
                                    {{ $etiqueta }} <span class="ml-1 rounded-full bg-black/10 px-1.5 py-0.5 text-xs">{{ $cantidad }}</span>
                                </button>
                            @endforeach
                        </section>
                    @endif

                    <section x-show="filtersOpen" x-collapse class="mt-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="font-bold text-neutral-900 dark:text-white">Filtros avanzados</h3>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Licenciatura → modalidad → generación → cuatrimestre.</p>
                            </div>
                            <button type="button" wire:click="resetFiltros" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-950/30">Restablecer</button>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                            <label class="xl:col-span-2">
                                <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Buscar</span>
                                <input type="search" wire:model.live.debounce.400ms="search" placeholder="Matrícula, CURP, nombre o licenciatura…"
                                    class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm text-neutral-900 outline-none focus:border-[#006492] focus:ring-2 focus:ring-sky-500/20 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Estado de generación</span>
                                <select wire:model.live="generacionEstado" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                    <option value="activas">Activas</option>
                                    <option value="finalizadas">Finalizadas</option>
                                    <option value="todas">Todas</option>
                                </select>
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Licenciatura</span>
                                <select wire:model.live="licenciaturaId" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                    <option value="">Todas</option>
                                    @foreach ($licenciaturas as $licenciatura)
                                        <option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre_corto ?: $licenciatura->nombre }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Modalidad</span>
                                <select wire:model.live="modalidadId" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                    <option value="">Todas</option>
                                    @foreach ($modalidades as $modalidad)
                                        <option value="{{ $modalidad->id }}">{{ $modalidad->nombre }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Generación</span>
                                <select wire:model.live="generacionId" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                    <option value="">Todas</option>
                                    @foreach ($generaciones as $generacion)
                                        <option value="{{ $generacion->id }}">{{ $generacion->generacion }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Cuatrimestre actual</span>
                                <select wire:model.live="cuatrimestreId" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                    <option value="">Todos</option>
                                    @foreach ($cuatrimestres as $cuatrimestre)
                                        <option value="{{ $cuatrimestre->id }}">{{ $cuatrimestre->nombre_cuatrimestre ?: $cuatrimestre->cuatrimestre . 'º' }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Sexo</span>
                                <select wire:model.live="sexo" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                    <option value="">Todos</option><option value="H">Hombres</option><option value="M">Mujeres</option>
                                </select>
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Procedencia</span>
                                <select wire:model.live="residencia" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                    <option value="">Todos</option><option value="local">Locales</option><option value="foraneo">Foráneos</option>
                                </select>
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Estado del alumno</span>
                                <select wire:model.live="estadoAlumno" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                    <option value="">Todos</option><option value="activo">Activos</option><option value="baja">Bajas</option>
                                </select>
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Inscrito desde</span>
                                <input type="date" wire:model.live="fechaDesde" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Inscrito hasta</span>
                                <input type="date" wire:model.live="fechaHasta" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                            </label>

                            @if ($modalTipo === 'bajos')
                                <label>
                                    <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Tipo de riesgo</span>
                                    <select wire:model.live="riesgoTipo" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                        <option value="todos">Numérica y NP</option><option value="numerica">Solo ≤ 6</option><option value="np">Solo NP / NA</option>
                                    </select>
                                </label>
                                <label>
                                    <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Materia</span>
                                    <select wire:model.live="materiaId" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                        <option value="">Todas</option>
                                        @foreach ($materias as $materia)<option value="{{ $materia->id }}">{{ $materia->nombre }}</option>@endforeach
                                    </select>
                                </label>
                                <label>
                                    <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Profesor</span>
                                    <select wire:model.live="profesorId" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                        <option value="">Todos</option>
                                        @foreach ($profesores as $profesor)<option value="{{ $profesor->id }}">{{ trim("{$profesor->apellido_paterno} {$profesor->apellido_materno} {$profesor->nombre}") }}</option>@endforeach
                                    </select>
                                </label>
                                <label>
                                    <span class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Cuatrimestre de la materia</span>
                                    <select wire:model.live="cuatrimestreAcademicoId" class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                                        <option value="">Todos</option>
                                        @foreach ($cuatrimestresAcademicos as $cuatrimestre)<option value="{{ $cuatrimestre->id }}">{{ $cuatrimestre->nombre_cuatrimestre ?: $cuatrimestre->cuatrimestre . 'º' }}</option>@endforeach
                                    </select>
                                </label>
                            @endif
                        </div>
                    </section>

                    <section x-show="chartOpen" x-collapse class="mt-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-neutral-900 dark:text-white">Distribución por licenciatura</h3>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">La gráfica responde a todos los filtros aplicados.</p>
                            </div>
                            <span class="rounded-full bg-lime-50 px-3 py-1 text-xs font-bold text-[#5f7e16] dark:bg-lime-950/40 dark:text-lime-200">ApexCharts</span>
                        </div>
                        <div wire:key="matriculas-chart-{{ $chartSignature }}" x-data="{ chart: null, stopped: false }"
                            x-init="const renderChart = () => {
                                if (stopped) return;
                                if (!window.ApexCharts || !$refs.chart) { setTimeout(renderChart, 50); return; }
                                const payload = @js($chartData);
                                const dark = document.documentElement.classList.contains('dark');
                                chart = new ApexCharts($refs.chart, {
                                    chart: { type: 'bar', height: Math.max(280, payload.categories.length * 42), fontFamily: 'Nunito, sans-serif', toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } } },
                                    series: [{ name: 'Alumnos', data: payload.series }],
                                    xaxis: { categories: payload.categories, labels: { style: { colors: dark ? '#d4d4d4' : '#525252' } }, title: { text: 'Cantidad de alumnos' } },
                                    yaxis: { labels: { maxWidth: 260, style: { colors: dark ? '#d4d4d4' : '#525252' } } },
                                    plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '62%', distributed: false } },
                                    colors: ['#006492'],
                                    dataLabels: { enabled: true, style: { fontWeight: 700 }, formatter: value => Math.round(value) },
                                    grid: { borderColor: dark ? '#404040' : '#e5e7eb', strokeDashArray: 4 },
                                    tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: value => `${value} alumno${value === 1 ? '' : 's'}` } },
                                    noData: { text: 'No hay registros para los filtros seleccionados.' }
                                });
                                chart.render();
                            };
                            $nextTick(renderChart);
                            $cleanup(() => { stopped = true; chart?.destroy(); })">
                            <div x-ref="chart" wire:ignore class="min-h-[280px] w-full"></div>
                        </div>
                    </section>

                    <section class="sticky top-0 z-20 mt-4 rounded-2xl border border-neutral-200 bg-white/95 p-3 shadow-sm backdrop-blur dark:border-neutral-700 dark:bg-neutral-800/95">
                        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-neutral-100 px-3 py-2 text-sm font-semibold text-neutral-700 dark:bg-neutral-700 dark:text-neutral-200">
                                    <input type="checkbox" wire:click="toggleSelectAllFiltered" @checked($selectAllFiltered) class="rounded border-neutral-300 text-[#006492] focus:ring-[#006492]">
                                    Seleccionar todos los filtrados
                                </label>
                                <span class="rounded-xl bg-sky-50 px-3 py-2 text-sm font-bold text-[#006492] dark:bg-sky-950/40 dark:text-sky-200">{{ $selectionCount }} seleccionados</span>
                                <label class="inline-flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-300">
                                    Mostrar
                                    <select wire:model.live="perPage" class="rounded-lg border border-neutral-300 bg-white px-2 py-1.5 dark:border-neutral-600 dark:bg-neutral-900">
                                        <option value="25">25</option><option value="50">50</option><option value="100">100</option>
                                    </select>
                                </label>
                            </div>

                            @can('admin.administracion')
                                <div x-data="{ open: false }" class="relative">
                                    <button type="button" @click="open = !open" class="inline-flex items-center gap-2 rounded-xl bg-[#006492] px-4 py-2.5 text-sm font-bold text-white shadow transition hover:bg-[#00557d]">
                                        Exportar reporte <span>▾</span>
                                    </button>
                                    <div x-show="open" x-transition @click.outside="open = false" class="absolute right-0 z-40 mt-2 w-64 overflow-hidden rounded-2xl bg-white p-2 shadow-2xl ring-1 ring-neutral-200 dark:bg-neutral-800 dark:ring-neutral-700">
                                        <p class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-neutral-400">Todos los filtrados</p>
                                        <button wire:click="exportar('excel','filtrados')" @click="open=false" class="w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-emerald-50 dark:hover:bg-emerald-950/30">Excel de resultados filtrados</button>
                                        <button wire:click="exportar('pdf','filtrados')" @click="open=false" class="w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-red-50 dark:hover:bg-red-950/30">PDF de resultados filtrados</button>
                                        <div class="my-2 border-t border-neutral-200 dark:border-neutral-700"></div>
                                        <p class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-neutral-400">Selección actual</p>
                                        <button wire:click="exportar('excel','seleccionados')" @click="open=false" @disabled($selectionCount === 0) class="w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-emerald-950/30">Excel de seleccionados</button>
                                        <button wire:click="exportar('pdf','seleccionados')" @click="open=false" @disabled($selectionCount === 0) class="w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-red-950/30">PDF de seleccionados</button>
                                    </div>
                                </div>
                            @endcan
                        </div>
                    </section>

                    <section class="relative mt-4 overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-800" wire:loading.class="opacity-60" wire:target="search,licenciaturaId,modalidadId,generacionId,cuatrimestreId,sexo,residencia,estadoAlumno,generacionEstado,fechaDesde,fechaHasta,materiaId,profesorId,cuatrimestreAcademicoId,riesgoTipo,sinCategoria,perPage">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gradient-to-r from-[#006492] to-sky-600 text-white">
                                    <tr>
                                        <th class="w-12 px-3 py-3 text-center">
                                            <input type="checkbox" wire:click="togglePagina('{{ implode(',', $pageIds) }}')" @checked($pageAllSelected) class="rounded border-white/50 text-[#88AC2E] focus:ring-white">
                                        </th>
                                        <th class="px-3 py-3 text-left text-[11px] uppercase tracking-wider">#</th>
                                        <th class="px-3 py-3 text-left text-[11px] uppercase tracking-wider">Matrícula</th>
                                        <th class="px-3 py-3 text-left text-[11px] uppercase tracking-wider">Alumno</th>
                                        <th class="px-3 py-3 text-left text-[11px] uppercase tracking-wider">Licenciatura</th>
                                        <th class="px-3 py-3 text-left text-[11px] uppercase tracking-wider">Datos académicos</th>
                                        @if ($modalTipo === 'bajos')
                                            <th class="min-w-[360px] px-3 py-3 text-left text-[11px] uppercase tracking-wider">Materias en riesgo</th>
                                        @else
                                            <th class="px-3 py-3 text-left text-[11px] uppercase tracking-wider">Estado</th>
                                        @endif
                                        <th class="sticky right-0 min-w-[230px] bg-sky-700 px-3 py-3 text-left text-[11px] uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                                    @forelse ($alumnos as $index => $alumno)
                                        @php
                                            $estadoMatricula = $this->matriculaEstado($alumno);
                                            $nombre = trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}");
                                            $matriculaClases = match($estadoMatricula) {
                                                'valida' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:ring-emerald-900',
                                                'duplicada' => 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-950/40 dark:text-red-200 dark:ring-red-900',
                                                'formato' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950/40 dark:text-amber-200 dark:ring-amber-900',
                                                default => 'bg-neutral-100 text-neutral-600 ring-neutral-200 dark:bg-neutral-700 dark:text-neutral-200 dark:ring-neutral-600',
                                            };
                                            $estadoEtiqueta = match($estadoMatricula) {
                                                'valida' => 'Válida', 'duplicada' => 'Duplicada', 'formato' => 'Formato incorrecto', default => 'Vacía'
                                            };
                                        @endphp
                                        <tr wire:key="matricula-row-{{ $alumno->id }}" class="align-top transition hover:bg-sky-50/60 dark:hover:bg-sky-950/20">
                                            <td class="px-3 py-3 text-center">
                                                <input type="checkbox" wire:click="toggleSeleccion({{ $alumno->id }})" @checked($this->isSelected($alumno->id)) class="rounded border-neutral-300 text-[#006492] focus:ring-[#006492]">
                                            </td>
                                            <td class="px-3 py-3 font-bold text-neutral-500">{{ ($alumnos->firstItem() ?? 1) + $index }}</td>
                                            <td class="px-3 py-3">
                                                <span class="inline-flex rounded-lg px-2 py-1 font-mono text-xs font-bold ring-1 {{ $matriculaClases }}">{{ $alumno->matricula ?: 'SIN MATRÍCULA' }}</span>
                                                <span class="mt-1 block text-[10px] font-semibold uppercase tracking-wide text-neutral-400">{{ $estadoEtiqueta }}</span>
                                            </td>
                                            <td class="px-3 py-3">
                                                <p class="font-bold text-neutral-900 dark:text-white">{{ $nombre ?: '—' }}</p>
                                                <p class="mt-1 font-mono text-[11px] text-neutral-500">{{ $alumno->CURP ?: 'Sin CURP' }}</p>
                                                <div class="mt-2 flex flex-wrap gap-1">
                                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $alumno->sexo === 'M' ? 'bg-fuchsia-50 text-fuchsia-700 dark:bg-fuchsia-950/30 dark:text-fuchsia-200' : 'bg-sky-50 text-sky-700 dark:bg-sky-950/30 dark:text-sky-200' }}">{{ $alumno->sexo === 'M' ? 'Mujer' : 'Hombre' }}</span>
                                                    <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-[10px] font-bold text-neutral-600 dark:bg-neutral-700 dark:text-neutral-200">{{ $alumno->foraneo === 'true' ? 'Foráneo' : 'Local' }}</span>
                                                </div>
                                            </td>
                                            <td class="max-w-[280px] px-3 py-3">
                                                <p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ optional($alumno->licenciatura)->nombre ?? '—' }}</p>
                                                @if (optional($alumno->licenciatura)->RVOE)
                                                    <p class="mt-1 text-[11px] text-neutral-500">RVOE: {{ $alumno->licenciatura->RVOE }}</p>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3">
                                                <div class="space-y-1.5 text-xs">
                                                    <p><span class="text-neutral-400">Modalidad:</span> <span class="rounded-full px-2 py-0.5 {{ $this->modalidadChip(optional($alumno->modalidad)->nombre) }}">{{ optional($alumno->modalidad)->nombre ?? '—' }}</span></p>
                                                    <p><span class="text-neutral-400">Generación:</span> <strong>{{ optional($alumno->generacion)->generacion ?? '—' }}</strong></p>
                                                    <p><span class="text-neutral-400">Cuatrimestre:</span> <strong>{{ optional($alumno->cuatrimestre)->nombre_cuatrimestre ?? optional($alumno->cuatrimestre)->cuatrimestre ?? '—' }}</strong></p>
                                                    <p><span class="text-neutral-400">Inscripción:</span> {{ optional($alumno->created_at)?->format('d/m/Y') ?? '—' }}</p>
                                                </div>
                                            </td>

                                            @if ($modalTipo === 'bajos')
                                                <td class="px-3 py-3">
                                                    <details class="group rounded-xl border border-red-100 bg-red-50/50 p-2 dark:border-red-900/50 dark:bg-red-950/20" open>
                                                        <summary class="cursor-pointer list-none text-xs font-bold text-red-700 dark:text-red-200">{{ $alumno->calificaciones->count() }} materia(s) en riesgo</summary>
                                                        <div class="mt-2 space-y-2">
                                                            @foreach ($alumno->calificaciones as $calificacion)
                                                                @php
                                                                    $valor = strtoupper(trim((string) $calificacion->calificacion));
                                                                    $profesor = trim(collect([
                                                                        optional($calificacion->profesor)->nombre,
                                                                        optional($calificacion->profesor)->apellido_paterno,
                                                                        optional($calificacion->profesor)->apellido_materno,
                                                                    ])->filter()->implode(' '));
                                                                @endphp
                                                                <div class="grid gap-1 rounded-lg bg-white p-2 ring-1 ring-red-100 dark:bg-neutral-800 dark:ring-red-900/50 sm:grid-cols-[1fr_auto]">
                                                                    <div>
                                                                        <p class="text-xs font-bold text-neutral-800 dark:text-neutral-100">{{ optional(optional($calificacion->asignacionMateria)->materia)->nombre ?? 'Materia no disponible' }}</p>
                                                                        <p class="mt-0.5 text-[10px] text-neutral-500">{{ optional(optional($calificacion->asignacionMateria)->cuatrimestre)->nombre_cuatrimestre ?? optional(optional($calificacion->asignacionMateria)->cuatrimestre)->cuatrimestre ?? '—' }} · {{ $profesor ?: 'Profesor no asignado' }}</p>
                                                                    </div>
                                                                    <span class="self-start rounded-lg bg-red-100 px-2 py-1 text-xs font-black text-red-700 dark:bg-red-950/50 dark:text-red-200">{{ $valor }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </details>
                                                </td>
                                            @else
                                                <td class="px-3 py-3">
                                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $alumno->status === 'true' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:ring-emerald-900' : 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-950/40 dark:text-red-200 dark:ring-red-900' }}">{{ $alumno->status === 'true' ? 'Activo' : 'Baja' }}</span>
                                                    @if ($alumno->fecha_baja)
                                                        <p class="mt-1 text-[10px] text-neutral-500">{{ \Illuminate\Support\Carbon::parse($alumno->fecha_baja)->format('d/m/Y') }}</p>
                                                    @endif
                                                </td>
                                            @endif

                                            <td class="sticky right-0 bg-white px-3 py-3 shadow-[-8px_0_12px_-12px_rgba(0,0,0,.35)] dark:bg-neutral-800">
                                                <div class="flex flex-wrap gap-1.5">
                                                    @can('admin.administracion')
                                                        <a href="{{ route('admin.pdf.expediente', $alumno->id) }}" target="_blank" class="rounded-lg bg-sky-50 px-2.5 py-1.5 text-[11px] font-bold text-[#006492] transition hover:bg-sky-100 dark:bg-sky-950/40 dark:text-sky-200">Expediente</a>
                                                        <a href="{{ route('admin.pdf.historial-academico-alumno', $alumno) }}" target="_blank" class="rounded-lg bg-lime-50 px-2.5 py-1.5 text-[11px] font-bold text-[#5f7e16] transition hover:bg-lime-100 dark:bg-lime-950/40 dark:text-lime-200">Historial</a>
                                                        <button type="button" wire:click="editarAlumno({{ $alumno->id }})" class="rounded-lg bg-violet-50 px-2.5 py-1.5 text-[11px] font-bold text-violet-700 transition hover:bg-violet-100 dark:bg-violet-950/40 dark:text-violet-200">Editar inscripción</button>
                                                        @if ($estadoMatricula !== 'valida')
                                                            <button type="button" wire:click="generarMatricula({{ $alumno->id }})" wire:confirm="Se reemplazará la matrícula actual por una matrícula institucional disponible. ¿Deseas continuar?" class="rounded-lg bg-amber-50 px-2.5 py-1.5 text-[11px] font-bold text-amber-700 transition hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-200">Generar matrícula</button>
                                                        @endif
                                                    @else
                                                        <span class="text-xs text-neutral-400">Solo consulta</span>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="px-4 py-16 text-center text-neutral-500">No se encontraron alumnos con los filtros seleccionados.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div wire:loading.flex wire:target="search,licenciaturaId,modalidadId,generacionId,cuatrimestreId,sexo,residencia,estadoAlumno,generacionEstado,fechaDesde,fechaHasta,materiaId,profesorId,cuatrimestreAcademicoId,riesgoTipo,sinCategoria,perPage" class="absolute inset-0 z-30 items-center justify-center bg-white/70 backdrop-blur-sm dark:bg-neutral-900/70">
                            <div class="flex items-center gap-3 rounded-xl bg-white px-4 py-3 shadow ring-1 ring-neutral-200 dark:bg-neutral-800 dark:ring-neutral-700">
                                <span class="h-5 w-5 animate-spin rounded-full border-2 border-[#006492] border-t-transparent"></span>
                                <span class="text-sm font-semibold">Aplicando filtros…</span>
                            </div>
                        </div>
                    </section>

                    <div class="mt-4 rounded-2xl border border-neutral-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-800">
                        {{ $alumnos->onEachSide(1)->links() }}
                    </div>
                </main>
            </div>
        </div>
    @endif

    @assets
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@6.5.0/dist/apexcharts.min.js"></script>
    @endassets

    <livewire:admin.licenciaturas.submodulo.matricula-editar />
</div>
