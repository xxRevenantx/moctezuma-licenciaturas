<div class="space-y-6" x-data="{ enviando: false }">
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="h-1.5 bg-gradient-to-r from-[#006492] via-sky-500 to-[#88AC2E]"></div>

        <div class="p-5 sm:p-7">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-[#006492] dark:bg-sky-950/40 dark:text-sky-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 2h9l5 5v15H6z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6M9 13h6M9 17h6" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[.18em] text-[#88AC2E]">Control escolar</p>
                            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white sm:text-3xl">Boletas de calificaciones</h1>
                        </div>
                    </div>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 dark:text-zinc-300">
                        Genera boletas por alumno, selección, cuatrimestre o generación completa. El sistema valida materias duplicadas,
                        detecta boletas incompletas y evita generar documentos vacíos.
                    </p>
                </div>

                <button type="button" wire:click="restablecer"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 101.7-5.3M3 4v5h5" />
                    </svg>
                    Restablecer
                </button>
            </div>
        </div>
    </section>

    {{-- Modo de trabajo --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white">1. ¿Cómo deseas generar las boletas?</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">El modo ajusta automáticamente la selección de alumnos y periodos.</p>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            @php
                $modos = [
                    'alumno' => ['titulo' => 'Por alumno', 'texto' => 'Un alumno y uno o todos sus cuatrimestres.', 'icono' => 'user'],
                    'seleccion' => ['titulo' => 'Selección múltiple', 'texto' => 'Elige alumnos específicos de la generación.', 'icono' => 'users'],
                    'cuatrimestre' => ['titulo' => 'Por cuatrimestre', 'texto' => 'Todos los alumnos activos de un periodo.', 'icono' => 'calendar'],
                    'generacion' => ['titulo' => 'Generación completa', 'texto' => 'Todos los alumnos y todos los periodos disponibles.', 'icono' => 'cap'],
                ];
            @endphp

            @foreach ($modos as $valor => $config)
                <button type="button" wire:click="$set('modo', '{{ $valor }}')"
                    class="group relative rounded-2xl border p-4 text-left transition
                        {{ $modo === $valor
                            ? 'border-[#006492] bg-sky-50 ring-2 ring-[#006492]/15 dark:bg-sky-950/30'
                            : 'border-slate-200 bg-white hover:border-sky-300 hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800' }}">
                    @if ($modo === $valor)
                        <span class="absolute right-3 top-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#006492] text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                    @endif
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:text-[#006492] dark:bg-zinc-800 dark:text-zinc-300">
                        @if ($config['icono'] === 'user')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 10-16 0M12 13a5 5 0 100-10 5 5 0 000 10z"/></svg>
                        @elseif ($config['icono'] === 'users')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                        @elseif ($config['icono'] === 'calendar')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v15H3V6a2 2 0 012-2z"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m2 10 10-5 10 5-10 5L2 10zM6 12.5V17c3 2 9 2 12 0v-4.5"/></svg>
                        @endif
                    </div>
                    <h3 class="mt-3 font-extrabold text-slate-900 dark:text-white">{{ $config['titulo'] }}</h3>
                    <p class="mt-1 pr-5 text-xs leading-5 text-slate-500 dark:text-zinc-400">{{ $config['texto'] }}</p>
                </button>
            @endforeach
        </div>
    </section>

    {{-- Filtros académicos · Flux UI --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <flux:heading size="lg" class="!font-black">2. Contexto académico</flux:heading>
                <flux:text class="mt-1 !text-sm !text-slate-500 dark:!text-zinc-400">
                    Solo se muestran licenciaturas, modalidades y generaciones que tienen alumnos activos.
                </flux:text>
            </div>

            <flux:badge color="green" class="w-fit">
                Opciones con alumnos activos
            </flux:badge>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <flux:field>
                <flux:label>Licenciatura</flux:label>
                <flux:select wire:model.live="licenciaturaId" class="w-full">
                    <flux:select.option value="">Selecciona una licenciatura</flux:select.option>
                    @foreach ($licenciaturas as $licenciatura)
                        <flux:select.option value="{{ $licenciatura['id'] }}">
                            {{ $licenciatura['nombre'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:description>Solo licenciaturas con alumnos activos.</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Modalidad</flux:label>
                <flux:select
                    wire:model.live="modalidadId"
                    class="w-full"
                    :disabled="!$licenciaturaId"
                >
                    <flux:select.option value="">
                        {{ $licenciaturaId ? 'Selecciona una modalidad' : 'Primero selecciona una licenciatura' }}
                    </flux:select.option>
                    @foreach ($modalidades as $modalidad)
                        <flux:select.option value="{{ $modalidad['id'] }}">
                            {{ $modalidad['nombre'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:description>
                    {{ $licenciaturaId ? 'Modalidades disponibles para la licenciatura.' : 'Se habilitará al elegir una licenciatura.' }}
                </flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Generación</flux:label>
                <flux:select
                    wire:model.live="generacionId"
                    class="w-full"
                    :disabled="!$modalidadId"
                >
                    <flux:select.option value="">
                        {{ $modalidadId ? 'Selecciona una generación' : 'Primero selecciona una modalidad' }}
                    </flux:select.option>
                    @foreach ($generaciones as $generacion)
                        <flux:select.option value="{{ $generacion['id'] }}">
                            {{ $generacion['generacion'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:description>
                    {{ $modalidadId ? 'Generaciones con alumnos activos.' : 'Se habilitará al elegir una modalidad.' }}
                </flux:description>
            </flux:field>

            <flux:field>
                <div class="flex items-center justify-between gap-2">
                    <flux:label>Cuatrimestre</flux:label>
                    @if ($modo === 'generacion')
                        <flux:badge color="green">Todos</flux:badge>
                    @endif
                </div>

                <flux:select
                    wire:model.live="cuatrimestreId"
                    class="w-full"
                    :disabled="!$generacionId || $modo === 'generacion'"
                >
                    <flux:select.option value="todos">Todos los cuatrimestres</flux:select.option>
                    @foreach ($cuatrimestres as $periodo)
                        <flux:select.option value="{{ $periodo['cuatrimestre_id'] }}">
                            {{ $periodo['nombre'] }} · {{ $periodo['ciclo_escolar'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:description>
                    @if ($modo === 'generacion')
                        La generación completa utiliza automáticamente todos los cuatrimestres disponibles.
                    @elseif ($generacionId)
                        Puedes elegir un periodo específico o todos los cuatrimestres.
                    @else
                        Se habilitará al elegir una generación.
                    @endif
                </flux:description>
            </flux:field>
        </div>

        @if ($generacionId && empty($cuatrimestres))
            <div class="mt-5">
                <flux:callout color="amber" icon="exclamation-triangle" class="w-full">
                    Esta generación tiene alumnos activos, pero no se encontraron periodos con materias calificables o calificaciones registradas.
                </flux:callout>
            </div>
        @endif
    </section>

    @if ($generacionId && count($alumnos))
        {{-- Estadística previa --}}
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Alumnos activos</p>
                <div class="mt-2 flex items-end justify-between">
                    <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $resumen['alumnos'] ?? 0 }}</span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-zinc-800 dark:text-zinc-300">{{ count($seleccionadosValidos) }} seleccionados</span>
                </div>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-300">Boletas completas</p>
                <p class="mt-2 text-3xl font-black text-emerald-700 dark:text-emerald-300">{{ $resumen['boletas_completas'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-300">Incompletas</p>
                <p class="mt-2 text-3xl font-black text-amber-700 dark:text-amber-300">{{ $resumen['boletas_incompletas'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 shadow-sm dark:border-rose-900/40 dark:bg-rose-950/20">
                <p class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-300">Sin calificaciones</p>
                <p class="mt-2 text-3xl font-black text-rose-700 dark:text-rose-300">{{ $resumen['boletas_sin_calificaciones'] ?? 0 }}</p>
            </div>
        </section>

        {{-- Selector de alumnos --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-slate-200 p-5 dark:border-zinc-700 sm:p-6">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">3. Selección de alumnos</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                            Ordenados por apellido paterno, apellido materno y nombre.
                            @if (in_array($modo, ['generacion', 'cuatrimestre'], true))
                                <span class="font-bold text-[#006492] dark:text-sky-300">Este modo incluye a todos los alumnos activos.</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="seleccionarTodos" @disabled($modo === 'alumno')
                            class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                            Seleccionar todos
                        </button>
                        <button type="button" wire:click="seleccionarResultados"
                            class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                            Seleccionar resultados
                        </button>
                        <button type="button" wire:click="limpiarSeleccion" @disabled(in_array($modo, ['generacion', 'cuatrimestre'], true))
                            class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                            Limpiar
                        </button>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto]">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input wire:model.live.debounce.250ms="search" type="search"
                            placeholder="Buscar por nombre, apellidos, matrícula o CURP..."
                            class="w-full rounded-xl border-slate-300 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm focus:border-[#006492] focus:ring-[#006492] dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" />
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach ([
                            'todos' => 'Todos',
                            'seleccionados' => 'Seleccionados',
                            'pendientes' => 'No seleccionados',
                            'completas' => 'Completas',
                            'incompletas' => 'Incompletas',
                            'sin_calificaciones' => 'Sin calificaciones',
                        ] as $valor => $texto)
                            <button type="button" wire:click="$set('filtroEstado', '{{ $valor }}')"
                                class="rounded-xl px-3 py-2 text-xs font-bold transition {{ $filtroEstado === $valor ? 'bg-[#006492] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">
                                {{ $texto }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="max-h-[510px] overflow-y-auto">
                @forelse ($alumnosVisibles as $alumno)
                    @php
                        $detalle = $analisis['alumnos'][$alumno['id']] ?? null;
                        $estado = $detalle['estado'] ?? 'sin_calificaciones';
                        $seleccionado = in_array((int) $alumno['id'], array_map('intval', $seleccionados), true);
                        $bloqueado = in_array($modo, ['generacion', 'cuatrimestre'], true);
                    @endphp
                    <button type="button" wire:click="toggleAlumno({{ $alumno['id'] }})" @disabled($bloqueado)
                        class="flex w-full items-center gap-4 border-b border-slate-100 px-5 py-4 text-left transition last:border-0
                            {{ $seleccionado ? 'bg-sky-50/70 dark:bg-sky-950/20' : 'bg-white hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/70' }}
                            disabled:cursor-default">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border-2 transition
                            {{ $seleccionado ? 'border-[#006492] bg-[#006492] text-white' : 'border-slate-300 bg-white text-transparent dark:border-zinc-600 dark:bg-zinc-800' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold uppercase text-slate-900 dark:text-white">{{ $alumno['nombre_completo'] }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-zinc-400">
                                        Matrícula: <span class="font-bold">{{ $alumno['matricula'] ?: 'Sin matrícula' }}</span>
                                        <span class="mx-1.5">·</span>
                                        CURP: {{ $alumno['CURP'] ?: 'Sin CURP' }}
                                    </p>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-black
                                        {{ $estado === 'completa'
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                                            : ($estado === 'incompleta'
                                                ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'
                                                : 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300') }}">
                                        {{ $estado === 'completa' ? 'Completa' : ($estado === 'incompleta' ? 'Incompleta' : 'Sin calificaciones') }}
                                    </span>
                                    @if ($detalle)
                                        <span class="text-xs font-bold text-slate-400">
                                            {{ $detalle['completas'] }}/{{ $detalle['total_periodos'] }} completas
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="p-10 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-zinc-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <p class="mt-3 font-bold text-slate-700 dark:text-zinc-200">No hay alumnos que coincidan con los filtros.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Exportación --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
            <div class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white">4. Formato de salida</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">El PDF individual y el envío por correo usan la misma lógica normalizada de calificaciones.</p>

                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        @foreach ([
                            'consolidado' => ['titulo' => 'PDF consolidado', 'texto' => 'Todas las boletas en un solo PDF.'],
                            'por_alumno' => ['titulo' => 'PDF por alumno', 'texto' => 'Une todos los periodos de cada alumno.'],
                            'zip' => ['titulo' => 'ZIP organizado', 'texto' => 'PDF individual por alumno y cuatrimestre.'],
                        ] as $valor => $config)
                            <button type="button" wire:click="$set('formato', '{{ $valor }}')"
                                class="rounded-2xl border p-4 text-left transition {{ $formato === $valor ? 'border-[#006492] bg-sky-50 ring-2 ring-[#006492]/10 dark:bg-sky-950/30' : 'border-slate-200 hover:bg-slate-50 dark:border-zinc-700 dark:hover:bg-zinc-800' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-extrabold text-slate-900 dark:text-white">{{ $config['titulo'] }}</p>
                                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-zinc-400">{{ $config['texto'] }}</p>
                                    </div>
                                    <span class="mt-0.5 h-4 w-4 rounded-full border-2 {{ $formato === $valor ? 'border-[#006492] bg-[#006492] ring-2 ring-white dark:ring-zinc-900' : 'border-slate-300 dark:border-zinc-600' }}"></span>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20">
                        <input type="checkbox" wire:model.live="incluirIncompletas"
                            class="mt-0.5 rounded border-amber-300 text-[#006492] focus:ring-[#006492]" />
                        <span>
                            <span class="block text-sm font-extrabold text-amber-900 dark:text-amber-200">Incluir boletas incompletas</span>
                            <span class="mt-1 block text-xs leading-5 text-amber-700 dark:text-amber-300">
                                Por seguridad, las boletas sin ninguna calificación nunca se generan. Si esta opción está desactivada, solo se generan boletas completas.
                            </span>
                        </span>
                    </label>
                </div>

                @php
                    $licSeleccionada = collect($licenciaturas)->firstWhere('id', $licenciaturaId);
                    $modSeleccionada = collect($modalidades)->firstWhere('id', $modalidadId);
                    $genSeleccionada = collect($generaciones)->firstWhere('id', $generacionId);
                    $cuatriSeleccionado = $cuatrimestreId === 'todos'
                        ? 'Todos los cuatrimestres'
                        : (collect($cuatrimestres)->firstWhere('cuatrimestre_id', (int) $cuatrimestreId)['nombre'] ?? 'Cuatrimestre');
                @endphp

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-zinc-700 dark:bg-zinc-800/70">
                    <p class="text-xs font-black uppercase tracking-[.16em] text-slate-400">Resumen de expedición</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-4"><dt class="text-slate-500 dark:text-zinc-400">Licenciatura</dt><dd class="text-right font-bold text-slate-900 dark:text-white">{{ $licSeleccionada['nombre'] ?? '—' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-slate-500 dark:text-zinc-400">Modalidad</dt><dd class="text-right font-bold text-slate-900 dark:text-white">{{ $modSeleccionada['nombre'] ?? '—' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-slate-500 dark:text-zinc-400">Generación</dt><dd class="text-right font-bold text-slate-900 dark:text-white">{{ $genSeleccionada['generacion'] ?? '—' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-slate-500 dark:text-zinc-400">Periodo</dt><dd class="text-right font-bold text-slate-900 dark:text-white">{{ $cuatriSeleccionado }}</dd></div>
                        <div class="border-t border-slate-200 pt-3 dark:border-zinc-700 flex items-start justify-between gap-4"><dt class="text-slate-500 dark:text-zinc-400">Alumnos</dt><dd class="text-right font-black text-[#006492] dark:text-sky-300">{{ count($seleccionadosValidos) }} seleccionados</dd></div>
                    </dl>

                    @if (count($seleccionadosValidos))
                        {{-- Vista previa siempre usa consolidado para que el navegador pueda mostrarla. --}}
                        <form action="{{ route('admin.boletas.exportar') }}" method="POST" target="_blank" class="mt-5">
                            @csrf
                            <input type="hidden" name="licenciatura_id" value="{{ $licenciaturaId }}">
                            <input type="hidden" name="modalidad_id" value="{{ $modalidadId }}">
                            <input type="hidden" name="generacion_id" value="{{ $generacionId }}">
                            <input type="hidden" name="cuatrimestre_id" value="{{ $cuatrimestreId }}">
                            <input type="hidden" name="formato" value="consolidado">
                            <input type="hidden" name="accion" value="preview">
                            <input type="hidden" name="incluir_incompletas" value="{{ $incluirIncompletas ? 1 : 0 }}">
                            @foreach ($seleccionadosValidos as $id)
                                <input type="hidden" name="alumno_ids[]" value="{{ $id }}">
                            @endforeach
                            <button type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-[#006492] bg-white px-4 py-2.5 text-sm font-extrabold text-[#006492] transition hover:bg-sky-50 dark:bg-zinc-900 dark:text-sky-300 dark:hover:bg-sky-950/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                Vista previa consolidada
                            </button>
                        </form>

                        <button type="button" wire:loading.attr="disabled" wire:target="enviarSeleccionados"
                            x-on:click="
                                Swal.fire({
                                    title: '¿Enviar boletas por correo?',
                                    text: 'Se enviará un correo por alumno con sus boletas válidas adjuntas.',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, enviar',
                                    cancelButtonText: 'Cancelar',
                                    confirmButtonColor: '#006492'
                                }).then((r) => { if (r.isConfirmed) { @this.call('enviarSeleccionados'); } });
                            "
                            class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                            <svg wire:loading.remove wire:target="enviarSeleccionados" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m22 2-7 20-4-9-9-4 20-7z"/><path d="M22 2 11 13"/></svg>
                            <svg wire:loading wire:target="enviarSeleccionados" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                            <span wire:loading.remove wire:target="enviarSeleccionados">Enviar por correo</span>
                            <span wire:loading wire:target="enviarSeleccionados">Encolando correos...</span>
                        </button>

                        <form action="{{ route('admin.boletas.exportar') }}" method="POST" class="mt-2"
                            x-on:submit="if (!confirm('Se generarán las boletas de {{ count($seleccionadosValidos) }} alumno(s). ¿Deseas continuar?')) { $event.preventDefault(); } else { enviando = true; setTimeout(() => enviando = false, 5000); }">
                            @csrf
                            <input type="hidden" name="licenciatura_id" value="{{ $licenciaturaId }}">
                            <input type="hidden" name="modalidad_id" value="{{ $modalidadId }}">
                            <input type="hidden" name="generacion_id" value="{{ $generacionId }}">
                            <input type="hidden" name="cuatrimestre_id" value="{{ $cuatrimestreId }}">
                            <input type="hidden" name="formato" value="{{ $formato }}">
                            <input type="hidden" name="accion" value="download">
                            <input type="hidden" name="incluir_incompletas" value="{{ $incluirIncompletas ? 1 : 0 }}">
                            @foreach ($seleccionadosValidos as $id)
                                <input type="hidden" name="alumno_ids[]" value="{{ $id }}">
                            @endforeach
                            <button type="submit" x-bind:disabled="enviando"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#006492] to-sky-600 px-4 py-3 text-sm font-black text-white shadow-sm transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60">
                                <svg x-show="!enviando" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/></svg>
                                <svg x-cloak x-show="enviando" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                <span x-text="enviando ? 'Generando documentos...' : 'Generar y descargar'"></span>
                            </button>
                        </form>
                    @else
                        <div class="mt-5 rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm font-bold text-slate-500 dark:border-zinc-600 dark:text-zinc-400">
                            Selecciona al menos un alumno para habilitar la exportación.
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @elseif ($generacionId)
        <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <p class="font-bold text-slate-700 dark:text-zinc-200">No se encontraron alumnos activos para este contexto.</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">La licenciatura dejará de aparecer cuando ya no tenga alumnos activos disponibles.</p>
        </section>
    @endif

    <div wire:loading.flex wire:target="licenciaturaId,modalidadId,generacionId,cuatrimestreId,modo"
        class="fixed inset-0 z-[90] items-center justify-center bg-slate-950/20 backdrop-blur-[1px]">
        <div class="flex items-center gap-3 rounded-2xl bg-white px-5 py-4 font-bold text-slate-700 shadow-xl dark:bg-zinc-900 dark:text-zinc-100">
            <svg class="h-5 w-5 animate-spin text-[#006492]" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
            Actualizando boletas...
        </div>
    </div>
</div>
