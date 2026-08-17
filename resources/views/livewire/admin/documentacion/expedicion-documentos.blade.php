<div
    x-data="{
        selectorMovil: false,
        confirmar: false,
        procesando: false,
        accion: 'preview',
        preparar(accion) {
            this.accion = accion;
            this.$refs.accion.value = accion;
            this.confirmar = true;
        },
        generar() {
            if (this.procesando) return;
            this.procesando = true;
            this.confirmar = false;
            this.$refs.form.submit();
            window.setTimeout(() => this.procesando = false, 1800);
        }
    }"
    class="space-y-5"
>
    <form
        x-ref="form"
        action="{{ route('admin.pdf.documentacion.documento_expedicion') }}"
        method="POST"
        target="_blank"
        class="space-y-5"
    >
        @csrf

        <input type="hidden" name="modo" value="{{ $modo }}">
        <input type="hidden" name="licenciatura_id" value="{{ $licenciatura_id }}">
        <input type="hidden" name="generacion_id" value="{{ $generacion_id }}">
        <input type="hidden" name="alcance_alumnos" value="{{ $alcance_alumnos }}">
        <input x-ref="accion" type="hidden" name="accion" value="preview">
        @foreach($alumno_ids as $alumnoIdSeleccionado)
            <input type="hidden" name="alumno_ids[]" value="{{ $alumnoIdSeleccionado }}">
        @endforeach

        {{-- Encabezado / modo de expedición --}}
        <section class="rounded-2xl border border-neutral-200 bg-neutral-50/70 p-4 dark:border-neutral-800 dark:bg-neutral-950/40 sm:p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.16em] text-[#006492] dark:text-sky-300">
                        Modo de expedición
                    </p>
                    <h3 class="mt-1 text-base font-semibold text-neutral-900 dark:text-white">
                        Elige cómo deseas preparar los documentos
                    </h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Puedes trabajar por licenciatura y alumnos, o generar la documentación completa de una generación.
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="restablecerTodo"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-neutral-200 bg-white px-3.5 py-2 text-sm font-medium text-neutral-700 shadow-sm transition hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Restablecer
                </button>
            </div>

            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <label class="relative cursor-pointer">
                    <input type="radio" wire:model.live="modo" value="licenciatura" class="peer sr-only">
                    <span class="flex h-full items-start gap-3 rounded-2xl border-2 border-neutral-200 bg-white p-4 transition peer-checked:border-[#006492] peer-checked:bg-[#006492]/5 dark:border-neutral-700 dark:bg-neutral-900 dark:peer-checked:border-sky-400 dark:peer-checked:bg-sky-400/5">
                        <span class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#006492]/10 text-[#006492] dark:text-sky-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-4 0H5m0 0H3m7 0v-5a2 2 0 012-2h0a2 2 0 012 2v5m-7-9h2m6 0h2M7 8h2m6 0h2" />
                            </svg>
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-neutral-900 dark:text-white">Por licenciatura y alumnos</span>
                            <span class="mt-1 block text-xs leading-5 text-neutral-500 dark:text-neutral-400">Selecciona una licenciatura, generación y uno o varios alumnos.</span>
                        </span>
                    </span>
                </label>

                <label class="relative cursor-pointer">
                    <input type="radio" wire:model.live="modo" value="generacion" class="peer sr-only">
                    <span class="flex h-full items-start gap-3 rounded-2xl border-2 border-neutral-200 bg-white p-4 transition peer-checked:border-[#88AC2E] peer-checked:bg-[#88AC2E]/5 dark:border-neutral-700 dark:bg-neutral-900 dark:peer-checked:border-lime-400 dark:peer-checked:bg-lime-400/5">
                        <span class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#88AC2E]/10 text-[#66851f] dark:text-lime-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </span>
                        <span>
                            <span class="flex items-center gap-2 text-sm font-semibold text-neutral-900 dark:text-white">
                                Generación completa
                                <span class="rounded-full bg-[#88AC2E]/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-[#58751a] dark:text-lime-300">Nuevo</span>
                            </span>
                            <span class="mt-1 block text-xs leading-5 text-neutral-500 dark:text-neutral-400">Genera registros y actas de todas las licenciaturas con alumnos activos en una generación.</span>
                        </span>
                    </span>
                </label>
            </div>
        </section>

        {{-- Indicador de pasos --}}
        <div class="hidden items-center gap-2 text-xs font-medium text-neutral-500 sm:flex dark:text-neutral-400">
            @php
                $paso1 = $modo === 'generacion' ? (bool) $generacion_id : (bool) $licenciatura_id;
                $paso2 = $modo === 'generacion' ? (bool) $generacion_id : (bool) $generacion_id;
                $paso3 = $modo === 'generacion' ? (bool) $generacion_id : count($alumno_ids) > 0;
                $paso4 = count($documentos) > 0;
            @endphp
            @foreach([
                ['ok' => $paso1, 'text' => $modo === 'generacion' ? 'Generación' : 'Licenciatura'],
                ['ok' => $paso2, 'text' => $modo === 'generacion' ? 'Licenciaturas' : 'Generación'],
                ['ok' => $paso3, 'text' => $modo === 'generacion' ? 'Alumnos' : 'Alumnos'],
                ['ok' => $paso4, 'text' => 'Documentos'],
            ] as $i => $paso)
                <span class="inline-flex items-center gap-1.5 {{ $paso['ok'] ? 'text-[#006492] dark:text-sky-300' : '' }}">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border {{ $paso['ok'] ? 'border-[#006492] bg-[#006492] text-white dark:border-sky-400 dark:bg-sky-400 dark:text-neutral-950' : 'border-neutral-300 bg-white dark:border-neutral-700 dark:bg-neutral-900' }}">
                        @if($paso['ok'])
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </span>
                    {{ $paso['text'] }}
                </span>
                @if(!$loop->last)
                    <span class="h-px w-5 bg-neutral-200 dark:bg-neutral-700"></span>
                @endif
            @endforeach
        </div>

        @if($modo === 'licenciatura')
            {{-- Datos académicos --}}
            <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-5">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">1. Datos académicos</h3>
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Solo aparecen licenciaturas y generaciones que tienen alumnos activos.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:select wire:model.live="licenciatura_id" label="Licenciatura" class="w-full">
                        <flux:select.option value="">Selecciona una licenciatura</flux:select.option>
                        @foreach($licenciaturas as $lic)
                            <flux:select.option value="{{ $lic->id }}">{{ $lic->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="generacion_id" label="Generación" class="w-full" :disabled="!$licenciatura_id">
                        <flux:select.option value="">{{ $licenciatura_id ? 'Selecciona una generación' : 'Primero selecciona una licenciatura' }}</flux:select.option>
                        @foreach($generaciones as $gen)
                            <flux:select.option value="{{ $gen->id }}">{{ $gen->generacion }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </section>

            {{-- Selector de alumnos --}}
            <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">2. Selección de alumnos</h3>
                            @if($generacion_id)
                                <span class="rounded-full bg-[#006492]/10 px-2.5 py-1 text-xs font-semibold text-[#006492] dark:text-sky-300">
                                    {{ $estadisticas['activos'] }} activos
                                </span>
                                @if($estadisticas['inactivos'] > 0)
                                    <span class="rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300">
                                        {{ $estadisticas['inactivos'] }} no incluidos
                                    </span>
                                @endif
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                            Orden alfabético: apellido paterno, apellido materno y nombre.
                        </p>
                    </div>

                    @if($generacion_id && $alumnos->isNotEmpty())
                        <div class="inline-flex rounded-xl bg-neutral-100 p-1 text-xs font-medium dark:bg-neutral-800">
                            <button type="button" wire:click="$set('filtroAlumno', 'todos')" class="rounded-lg px-3 py-1.5 transition {{ $filtroAlumno === 'todos' ? 'bg-white text-neutral-900 shadow-sm dark:bg-neutral-700 dark:text-white' : 'text-neutral-500 dark:text-neutral-400' }}">Todos</button>
                            <button type="button" wire:click="$set('filtroAlumno', 'seleccionados')" class="rounded-lg px-3 py-1.5 transition {{ $filtroAlumno === 'seleccionados' ? 'bg-white text-neutral-900 shadow-sm dark:bg-neutral-700 dark:text-white' : 'text-neutral-500 dark:text-neutral-400' }}">Seleccionados</button>
                            <button type="button" wire:click="$set('filtroAlumno', 'no-seleccionados')" class="rounded-lg px-3 py-1.5 transition {{ $filtroAlumno === 'no-seleccionados' ? 'bg-white text-neutral-900 shadow-sm dark:bg-neutral-700 dark:text-white' : 'text-neutral-500 dark:text-neutral-400' }}">Pendientes</button>
                        </div>
                    @endif
                </div>

                @if(!$generacion_id)
                    <div class="mt-4 flex min-h-44 items-center justify-center rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-6 text-center dark:border-neutral-700 dark:bg-neutral-950/50">
                        <div>
                            <span class="mx-auto inline-flex h-11 w-11 items-center justify-center rounded-full bg-neutral-200/70 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m4-4a4 4 0 100-8 4 4 0 000 8zM7 10a3 3 0 100-6 3 3 0 000 6z" /></svg>
                            </span>
                            <p class="mt-3 text-sm font-medium text-neutral-700 dark:text-neutral-200">Selecciona una licenciatura y generación</p>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Aquí aparecerán los alumnos disponibles para la expedición.</p>
                        </div>
                    </div>
                @elseif($alumnos->isEmpty())
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
                        No hay alumnos activos disponibles en esta combinación. La licenciatura/generación no puede utilizarse para expedir documentos.
                    </div>
                @else
                    <div class="mt-4 grid gap-3 lg:grid-cols-[1fr_auto]">
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <input
                                type="search"
                                wire:model.live.debounce.250ms="busquedaAlumno"
                                placeholder="Buscar por nombre, apellidos, matrícula o CURP..."
                                class="w-full rounded-xl border border-neutral-200 bg-white py-2.5 pl-9 pr-3 text-sm text-neutral-900 outline-none transition focus:border-[#006492] focus:ring-2 focus:ring-[#006492]/15 dark:border-neutral-700 dark:bg-neutral-950 dark:text-white"
                            >
                        </div>

                        <div class="hidden gap-2 md:flex">
                            <button type="button" wire:click="seleccionarResultados" class="rounded-xl border border-neutral-200 px-3 py-2 text-xs font-semibold text-neutral-700 transition hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">Seleccionar resultados</button>
                            <button type="button" wire:click="seleccionarTodosAlumnos" class="rounded-xl bg-[#006492] px-3 py-2 text-xs font-semibold text-white transition hover:brightness-95">Seleccionar todos</button>
                            <button type="button" wire:click="limpiarSeleccion" class="rounded-xl border border-neutral-200 px-3 py-2 text-xs font-semibold text-neutral-700 transition hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">Limpiar</button>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between rounded-xl bg-neutral-50 px-3 py-2 text-xs dark:bg-neutral-950/50">
                        <span class="text-neutral-500 dark:text-neutral-400">{{ $this->alumnosFiltrados->count() }} resultados visibles</span>
                        <span class="font-semibold text-[#006492] dark:text-sky-300">{{ count($alumno_ids) }} de {{ $alumnos->count() }} seleccionados</span>
                    </div>

                    {{-- Escritorio --}}
                    <div class="mt-3 hidden max-h-80 overflow-y-auto rounded-2xl border border-neutral-200 md:block dark:border-neutral-800">
                        <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            @forelse($this->alumnosFiltrados as $alumno)
                                <label class="flex cursor-pointer items-center gap-3 px-4 py-3 transition hover:bg-neutral-50 dark:hover:bg-neutral-800/70">
                                    <input
                                        type="checkbox"
                                        wire:model.live="alumno_ids"
                                        value="{{ $alumno->id }}"
                                        class="h-4 w-4 rounded border-neutral-300 text-[#006492] focus:ring-[#006492] dark:border-neutral-600 dark:bg-neutral-800"
                                    >
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold uppercase text-neutral-800 dark:text-neutral-100">
                                            {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}
                                        </span>
                                        <span class="mt-0.5 flex flex-wrap gap-x-3 gap-y-1 text-xs text-neutral-500 dark:text-neutral-400">
                                            <span>Matrícula: <strong class="font-medium text-neutral-700 dark:text-neutral-300">{{ $alumno->matricula }}</strong></span>
                                            <span class="hidden xl:inline">CURP: {{ $alumno->CURP }}</span>
                                        </span>
                                    </span>
                                </label>
                            @empty
                                <div class="p-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No hay alumnos que coincidan con la búsqueda o filtro.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Móvil: abre selector a pantalla completa --}}
                    <button type="button" @click="selectorMovil = true" class="mt-3 flex w-full items-center justify-between rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-3 text-left md:hidden dark:border-neutral-700 dark:bg-neutral-950/50">
                        <span>
                            <span class="block text-sm font-semibold text-neutral-800 dark:text-neutral-100">Abrir selector de alumnos</span>
                            <span class="mt-0.5 block text-xs text-neutral-500">{{ count($alumno_ids) }} seleccionados</span>
                        </span>
                        <svg class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>

                    <div x-show="selectorMovil" x-cloak class="fixed inset-0 z-[80] md:hidden" role="dialog" aria-modal="true">
                        <div class="absolute inset-0 bg-black/50" @click="selectorMovil = false"></div>
                        <div class="absolute inset-x-0 bottom-0 max-h-[92vh] overflow-hidden rounded-t-3xl bg-white shadow-2xl dark:bg-neutral-900">
                            <div class="flex items-center justify-between border-b border-neutral-200 px-4 py-3 dark:border-neutral-800">
                                <div>
                                    <p class="text-sm font-semibold text-neutral-900 dark:text-white">Seleccionar alumnos</p>
                                    <p class="text-xs text-neutral-500">{{ count($alumno_ids) }} de {{ $alumnos->count() }} seleccionados</p>
                                </div>
                                <button type="button" @click="selectorMovil = false" class="rounded-lg p-2 text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="space-y-3 p-4">
                                <input type="search" wire:model.live.debounce.250ms="busquedaAlumno" placeholder="Nombre, matrícula o CURP..." class="w-full rounded-xl border border-neutral-200 bg-white px-3 py-2.5 text-sm dark:border-neutral-700 dark:bg-neutral-950 dark:text-white">
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" wire:click="seleccionarResultados" class="rounded-lg border border-neutral-200 px-2 py-2 text-[11px] font-semibold dark:border-neutral-700">Resultados</button>
                                    <button type="button" wire:click="seleccionarTodosAlumnos" class="rounded-lg bg-[#006492] px-2 py-2 text-[11px] font-semibold text-white">Todos</button>
                                    <button type="button" wire:click="limpiarSeleccion" class="rounded-lg border border-neutral-200 px-2 py-2 text-[11px] font-semibold dark:border-neutral-700">Limpiar</button>
                                </div>
                            </div>

                            <div class="max-h-[58vh] overflow-y-auto border-y border-neutral-100 dark:border-neutral-800">
                                @forelse($this->alumnosFiltrados as $alumno)
                                    <label class="flex items-center gap-3 border-b border-neutral-100 px-4 py-3 last:border-0 dark:border-neutral-800">
                                        <input type="checkbox" wire:model.live="alumno_ids" value="{{ $alumno->id }}" class="h-4 w-4 rounded text-[#006492]">
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-xs font-semibold uppercase text-neutral-800 dark:text-neutral-100">{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</span>
                                            <span class="mt-0.5 block text-[11px] text-neutral-500">{{ $alumno->matricula }}</span>
                                        </span>
                                    </label>
                                @empty
                                    <div class="p-8 text-center text-sm text-neutral-500">Sin coincidencias.</div>
                                @endforelse
                            </div>

                            <div class="p-4">
                                <button type="button" @click="selectorMovil = false" class="w-full rounded-xl bg-[#006492] px-4 py-3 text-sm font-semibold text-white">Listo</button>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        @else
            {{-- Generación completa --}}
            <section class="rounded-2xl border border-[#88AC2E]/30 bg-[#88AC2E]/5 p-4 shadow-sm dark:border-lime-500/20 dark:bg-lime-500/5 sm:p-5">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(320px,.7fr)] lg:items-end">
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">1. Generación completa</h3>
                        <p class="mt-1 text-xs leading-5 text-neutral-500 dark:text-neutral-400">
                            Se incluirán automáticamente todas las licenciaturas que tengan alumnos activos en la generación seleccionada. Las licenciaturas sin alumnos quedan excluidas.
                        </p>
                        <div class="mt-4 max-w-xl">
                            <flux:select wire:model.live="generacion_id" label="Generación" class="w-full">
                                <flux:select.option value="">Selecciona una generación</flux:select.option>
                                @foreach($generacionesGlobales as $gen)
                                    <flux:select.option value="{{ $gen->id }}">{{ $gen->generacion }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>

                    @if($generacion_id)
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-white/70 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                                <p class="text-2xl font-bold text-[#006492] dark:text-sky-300">{{ $this->resumenGeneracionCompleta['licenciaturas'] }}</p>
                                <p class="mt-1 text-xs font-medium text-neutral-500">Licenciaturas incluidas</p>
                            </div>
                            <div class="rounded-2xl border border-white/70 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                                <p class="text-2xl font-bold text-[#66851f] dark:text-lime-300">{{ $this->resumenGeneracionCompleta['alumnos'] }}</p>
                                <p class="mt-1 text-xs font-medium text-neutral-500">Alumnos activos</p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- Documentos --}}
        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-5">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $modo === 'generacion' ? '2' : '3' }}. Documentos a generar</h3>
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Puedes elegir uno o ambos documentos.</p>
                </div>
                @if($modo === 'generacion')
                    <button
                        type="button"
                        wire:click="$set('documentos', ['registro-escolaridad', 'acta-resultados'])"
                        class="mt-2 inline-flex items-center gap-1.5 self-start rounded-lg bg-[#88AC2E]/10 px-3 py-1.5 text-xs font-semibold text-[#58751a] transition hover:bg-[#88AC2E]/20 dark:text-lime-300 sm:mt-0"
                    >
                        Seleccionar registros y actas
                    </button>
                @endif
            </div>

            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <label class="cursor-pointer">
                    <input type="checkbox" wire:model.live="documentos" name="documentos[]" value="registro-escolaridad" class="peer sr-only">
                    <span class="relative flex h-full items-start gap-3 rounded-2xl border-2 border-neutral-200 p-4 transition peer-checked:border-[#006492] peer-checked:bg-[#006492]/5 dark:border-neutral-700 dark:peer-checked:border-sky-400 dark:peer-checked:bg-sky-400/5">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#006492]/10 text-[#006492] dark:text-sky-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h7l4 4v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold text-neutral-900 dark:text-white">Registro de Escolaridad</span>
                            <span class="mt-1 block text-xs leading-5 text-neutral-500 dark:text-neutral-400">Concentrado académico oficial por periodos y cuatrimestres.</span>
                        </span>
                        <span class="absolute right-3 top-3 inline-flex h-5 w-5 items-center justify-center rounded-full border border-neutral-300 text-transparent peer-checked:border-[#006492] peer-checked:bg-[#006492] peer-checked:text-white dark:border-neutral-600">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                        </span>
                    </span>
                </label>

                <label class="cursor-pointer">
                    <input type="checkbox" wire:model.live="documentos" name="documentos[]" value="acta-resultados" class="peer sr-only">
                    <span class="relative flex h-full items-start gap-3 rounded-2xl border-2 border-neutral-200 p-4 transition peer-checked:border-[#88AC2E] peer-checked:bg-[#88AC2E]/5 dark:border-neutral-700 dark:peer-checked:border-lime-400 dark:peer-checked:bg-lime-400/5">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#88AC2E]/10 text-[#66851f] dark:text-lime-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5a3 3 0 016 0m-7 8l2 2 4-4" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold text-neutral-900 dark:text-white">Acta de Resultados</span>
                            <span class="mt-1 block text-xs leading-5 text-neutral-500 dark:text-neutral-400">Actas de evaluación correspondientes a las materias de la licenciatura.</span>
                        </span>
                        <span class="absolute right-3 top-3 inline-flex h-5 w-5 items-center justify-center rounded-full border border-neutral-300 text-transparent peer-checked:border-[#88AC2E] peer-checked:bg-[#88AC2E] peer-checked:text-white dark:border-neutral-600">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                        </span>
                    </span>
                </label>
            </div>

            @if(count($documentos) === 0)
                <p class="mt-3 text-xs font-medium text-red-600 dark:text-red-400">Selecciona al menos un tipo de documento.</p>
            @endif
        </section>

        {{-- Formato de salida --}}
        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $modo === 'generacion' ? '3' : '4' }}. Formato de salida</h3>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="salida" name="salida" value="consolidado" class="peer sr-only">
                    <span class="flex items-start gap-3 rounded-xl border border-neutral-200 p-3 transition peer-checked:border-[#006492] peer-checked:bg-[#006492]/5 dark:border-neutral-700 dark:peer-checked:border-sky-400">
                        <span class="mt-0.5 h-4 w-4 rounded-full border-4 border-white bg-neutral-300 ring-1 ring-neutral-300 peer-checked:bg-[#006492]"></span>
                        <span><span class="block text-sm font-semibold text-neutral-800 dark:text-neutral-100">PDF consolidado</span><span class="mt-0.5 block text-xs text-neutral-500">Integra los documentos seleccionados en un solo PDF.</span></span>
                    </span>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="salida" name="salida" value="separados" class="peer sr-only">
                    <span class="flex items-start gap-3 rounded-xl border border-neutral-200 p-3 transition peer-checked:border-[#88AC2E] peer-checked:bg-[#88AC2E]/5 dark:border-neutral-700 dark:peer-checked:border-lime-400">
                        <span class="mt-0.5 h-4 w-4 rounded-full border-4 border-white bg-neutral-300 ring-1 ring-neutral-300 peer-checked:bg-[#88AC2E]"></span>
                        <span><span class="block text-sm font-semibold text-neutral-800 dark:text-neutral-100">Archivos separados</span><span class="mt-0.5 block text-xs text-neutral-500">Al descargar varios documentos se entregan organizados en un ZIP.</span></span>
                    </span>
                </label>
            </div>
        </section>

        {{-- Resumen y acciones --}}
        @php
            $puedeGenerar = count($documentos) > 0
                && $generacion_id
                && ($modo === 'generacion' || ($licenciatura_id && count($alumno_ids) > 0));

            $documentosTexto = collect($documentos)->map(fn($doc) => $doc === 'registro-escolaridad' ? 'Registro de Escolaridad' : 'Acta de Resultados')->implode(' + ');
        @endphp

        <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-950 text-white shadow-sm dark:border-neutral-800">
            <div class="grid gap-5 p-4 sm:p-5 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.15em] text-sky-300">Resumen de expedición</p>
                    <div class="mt-3 grid gap-x-8 gap-y-2 text-sm sm:grid-cols-2">
                        @if($modo === 'licenciatura')
                            <div><span class="text-neutral-400">Licenciatura:</span> <span class="font-medium">{{ $this->licenciaturaSeleccionada?->nombre ?? 'Pendiente' }}</span></div>
                            <div><span class="text-neutral-400">Generación:</span> <span class="font-medium">{{ $this->generacionSeleccionada?->generacion ?? 'Pendiente' }}</span></div>
                            <div><span class="text-neutral-400">Alumnos:</span> <span class="font-medium">{{ count($alumno_ids) }} seleccionados</span></div>
                        @else
                            <div><span class="text-neutral-400">Alcance:</span> <span class="font-medium">Generación completa</span></div>
                            <div><span class="text-neutral-400">Generación:</span> <span class="font-medium">{{ $this->generacionSeleccionada?->generacion ?? 'Pendiente' }}</span></div>
                            <div><span class="text-neutral-400">Incluye:</span> <span class="font-medium">{{ $this->resumenGeneracionCompleta['licenciaturas'] }} licenciaturas · {{ $this->resumenGeneracionCompleta['alumnos'] }} alumnos</span></div>
                        @endif
                        <div><span class="text-neutral-400">Documentos:</span> <span class="font-medium">{{ $documentosTexto ?: 'Pendiente' }}</span></div>
                        <div><span class="text-neutral-400">Salida:</span> <span class="font-medium">{{ $salida === 'consolidado' ? 'PDF consolidado' : 'Archivos separados' }}</span></div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                    <button
                        type="button"
                        @click="preparar('preview')"
                        @disabled(!$puedeGenerar)
                        class="inline-flex min-w-36 items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/15 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c-1.5 4-4.5 6-9 6s-7.5-2-9-6c1.5-4 4.5-6 9-6s7.5 2 9 6z" /></svg>
                        Vista previa
                    </button>
                    <button
                        type="button"
                        @click="preparar('download')"
                        @disabled(!$puedeGenerar)
                        class="inline-flex min-w-36 items-center justify-center gap-2 rounded-xl bg-[#88AC2E] px-4 py-2.5 text-sm font-bold text-white transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M5 20h14" /></svg>
                        Descargar
                    </button>
                </div>
            </div>
        </section>
    </form>

    {{-- Confirmación --}}
    <div x-show="confirmar" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/55 backdrop-blur-[1px]" @click="confirmar = false"></div>
        <div x-show="confirmar" x-transition class="relative w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl dark:bg-neutral-900">
            <div class="border-b border-neutral-100 p-5 dark:border-neutral-800">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#006492]/10 text-[#006492] dark:text-sky-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h7l4 4v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-white">Confirmar expedición</h3>
                        <p class="mt-1 text-sm leading-6 text-neutral-500 dark:text-neutral-400">
                            @if($modo === 'generacion')
                                Se procesarán {{ $this->resumenGeneracionCompleta['licenciaturas'] }} licenciaturas y {{ $this->resumenGeneracionCompleta['alumnos'] }} alumnos activos de la generación {{ $this->generacionSeleccionada?->generacion }}.
                            @else
                                Se procesarán {{ count($alumno_ids) }} alumnos de {{ $this->licenciaturaSeleccionada?->nombre }}, generación {{ $this->generacionSeleccionada?->generacion }}.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="rounded-2xl bg-neutral-50 p-4 text-sm dark:bg-neutral-950/50">
                    <div class="flex justify-between gap-4"><span class="text-neutral-500">Documentos</span><span class="text-right font-semibold text-neutral-800 dark:text-neutral-100">{{ $documentosTexto }}</span></div>
                    <div class="mt-2 flex justify-between gap-4"><span class="text-neutral-500">Acción</span><span class="font-semibold text-neutral-800 dark:text-neutral-100" x-text="accion === 'preview' ? 'Abrir vista previa' : 'Descargar archivos'"></span></div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="confirmar = false" class="rounded-xl border border-neutral-200 px-4 py-2.5 text-sm font-semibold text-neutral-700 dark:border-neutral-700 dark:text-neutral-200">Cancelar</button>
                    <button type="button" @click="generar()" class="inline-flex items-center gap-2 rounded-xl bg-[#006492] px-4 py-2.5 text-sm font-semibold text-white">
                        Confirmar y generar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bloqueo breve para evitar doble clic --}}
    <div x-show="procesando" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/35 p-4 backdrop-blur-[1px]">
        <div class="flex items-center gap-3 rounded-2xl bg-white px-5 py-4 shadow-2xl dark:bg-neutral-900">
            <svg class="h-5 w-5 animate-spin text-[#006492]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
            <div>
                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Generando documentos…</p>
                <p class="text-xs text-neutral-500">Se abrirán en una nueva pestaña.</p>
            </div>
        </div>
    </div>
</div>
