<div
    x-data="{
        toast: null,
        tipo: 'ok',
        timer: null,
        selectorAbierto: false,
        confirmacionAbierta: false,
        buscarProfesor: '',
        mostrarToast() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.toast = null, 3600);
        },
        normalizar(texto) {
            return (texto ?? '')
                .toString()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();
        },
        coincideProfesor(texto) {
            const termino = this.normalizar(this.buscarProfesor);
            return termino === '' || this.normalizar(texto).includes(termino);
        }
    }"
    @asignacion-docente-actualizada.window="toast = $event.detail.message; tipo = 'ok'; mostrarToast()"
    @asignacion-docente-error.window="toast = $event.detail.message; tipo = 'error'; mostrarToast()"
    @abrir-selector-profesor.window="selectorAbierto = true; buscarProfesor = ''; $nextTick(() => $refs.buscarProfesor?.focus())"
    @cerrar-selector-profesor.window="selectorAbierto = false; buscarProfesor = ''"
    @asignacion-requiere-confirmacion.window="selectorAbierto = false; confirmacionAbierta = true"
    @cerrar-confirmacion-asignacion.window="confirmacionAbierta = false"
    @keydown.escape.window="
        if (confirmacionAbierta) {
            confirmacionAbierta = false;
            $wire.cancelarOperacionPendiente();
        } else if (selectorAbierto) {
            selectorAbierto = false;
            buscarProfesor = '';
        }
    "
    class="space-y-5"
>
    @php
        $esColorClaro = static function (?string $hex): bool {
            if (!$hex || !preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
                return false;
            }

            $r = hexdec(substr($hex, 1, 2));
            $g = hexdec(substr($hex, 3, 2));
            $b = hexdec(substr($hex, 5, 2));
            $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

            return $luminance > 0.6;
        };

        $nombreProfesor = static fn ($profesor) => trim(collect([
            $profesor?->apellido_paterno,
            $profesor?->apellido_materno,
            $profesor?->nombre,
        ])->filter()->implode(' '));

        $filtrosActivos = collect([
            $search,
            $filtrar_cuatrimestre,
            $filtrar_asignacion,
            $filtrar_profesor,
            $filtrar_calificable,
        ])->filter(fn ($valor) => $valor !== '' && $valor !== null)->count();

        $idsPagina = collect($materias->items())->pluck('id')->map(fn ($id) => (string) $id);
        $seleccionadas = collect($materias_seleccionadas)->map(fn ($id) => (string) $id)->unique();
        $paginaCompletaSeleccionada = $idsPagina->isNotEmpty()
            && $idsPagina->every(fn ($id) => $seleccionadas->contains($id));

        $profesorMasivo = $profesor_masivo !== ''
            ? $profesores->firstWhere('id', (int) $profesor_masivo)
            : null;

        $profesorDestinoPendiente = array_key_exists('profesor_id', $operacion_pendiente)
            && $operacion_pendiente['profesor_id']
                ? $profesores->firstWhere('id', (int) $operacion_pendiente['profesor_id'])
                : null;

        $totalSeleccionadas = $seleccionadas->count();
    @endphp

    {{-- Toast de confirmación/error --}}
    <div
        x-cloak
        x-show="toast"
        x-transition.opacity.duration.200ms
        class="fixed right-5 top-5 z-[90] max-w-sm"
        role="status"
        aria-live="polite"
    >
        <div
            class="flex items-start gap-3 rounded-2xl border px-4 py-3 shadow-xl backdrop-blur"
            :class="tipo === 'ok'
                ? 'border-emerald-200 bg-white/95 text-emerald-900 dark:border-emerald-800 dark:bg-neutral-900/95 dark:text-emerald-200'
                : 'border-rose-200 bg-white/95 text-rose-900 dark:border-rose-800 dark:bg-neutral-900/95 dark:text-rose-200'"
        >
            <div
                class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-full"
                :class="tipo === 'ok' ? 'bg-emerald-100 dark:bg-emerald-900/40' : 'bg-rose-100 dark:bg-rose-900/40'"
            >
                <svg x-show="tipo === 'ok'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                </svg>
                <svg x-show="tipo === 'error'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M12 8v5m0 3h.01"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.3 3.8 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold" x-text="tipo === 'ok' ? 'Asignación actualizada' : 'No se pudo completar'"> </p>
                <p class="mt-0.5 text-sm opacity-80" x-text="toast"></p>
            </div>
        </div>
    </div>

    {{-- Indicador no intrusivo --}}
    <div
        wire:loading.delay.flex
        wire:target="seleccionarProfesorDesdePicker, seleccionarSinProfesorDesdePicker, prepararAsignacionMasiva, confirmarOperacionPendiente"
        class="fixed bottom-5 right-5 z-[80] items-center gap-2 rounded-full border border-sky-200 bg-white/95 px-4 py-2 text-sm font-semibold text-[#006492] shadow-lg backdrop-blur dark:border-sky-900 dark:bg-neutral-900/95 dark:text-sky-300"
    >
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
        </svg>
        Revisando y guardando…
    </div>

    {{-- Resumen rápido --}}
    <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Resumen de asignación de materias">
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Materias</p>
                    <p class="mt-1 text-2xl font-black text-slate-900 dark:text-white">{{ $resumen['total'] }}</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-sky-50 text-[#006492] dark:bg-sky-950/40 dark:text-sky-300">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"/>
                        <path stroke-linecap="round" d="M4 5.5v16"/>
                    </svg>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 shadow-sm dark:border-emerald-900/60 dark:bg-emerald-950/20">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">Asignadas</p>
                    <p class="mt-1 text-2xl font-black text-emerald-900 dark:text-emerald-100">{{ $resumen['asignadas'] }}</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                    </svg>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 shadow-sm dark:border-amber-900/60 dark:bg-amber-950/20">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-700 dark:text-amber-300">Pendientes</p>
                    <p class="mt-1 text-2xl font-black text-amber-900 dark:text-amber-100">{{ $resumen['sin_asignar'] }}</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 1.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-lime-200 bg-lime-50/60 p-4 shadow-sm dark:border-lime-900/60 dark:bg-lime-950/20">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-lime-700 dark:text-lime-300">Docentes</p>
                    <p class="mt-1 text-2xl font-black text-lime-900 dark:text-lime-100">{{ $resumen['docentes'] }}</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-lime-100 text-[#5D7C17] dark:bg-lime-900/40 dark:text-lime-300">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-1 2 2 4-4"/>
                    </svg>
                </span>
            </div>
        </article>
    </section>

    {{-- Avance por cuatrimestre --}}
    @if(count($progresoCuatrimestres))
        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Avance por cuatrimestre</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Útil para detectar rápidamente qué periodos todavía tienen materias sin docente.</p>
                </div>
                <span class="text-xs font-semibold text-[#006492] dark:text-sky-300">{{ $modalidad->nombre }}</span>
            </div>

            <div class="flex gap-3 overflow-x-auto pb-1">
                @foreach($progresoCuatrimestres as $avance)
                    <button
                        type="button"
                        wire:click="filtrarPorCuatrimestre({{ $avance['id'] }})"
                        class="min-w-[180px] cursor-pointer rounded-xl border px-3 py-3 text-left transition hover:-translate-y-0.5 hover:shadow-sm
                            {{ (string) $filtrar_cuatrimestre === (string) $avance['id']
                                ? 'border-sky-300 bg-sky-50/80 dark:border-sky-800 dark:bg-sky-950/30'
                                : 'border-slate-200 bg-slate-50/60 hover:border-sky-200 dark:border-neutral-800 dark:bg-neutral-950/30 dark:hover:border-sky-900' }}"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $avance['nombre'] }}</span>
                            <span class="text-xs font-black {{ $avance['porcentaje'] === 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $avance['porcentaje'] }}%
                            </span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-neutral-800">
                            <div
                                class="h-full rounded-full {{ $avance['porcentaje'] === 100 ? 'bg-emerald-500' : 'bg-[#006492]' }}"
                                style="width: {{ min($avance['porcentaje'], 100) }}%"
                            ></div>
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $avance['asignadas'] }}/{{ $avance['total'] }}</span> asignadas
                            @if($avance['pendientes'] > 0)
                                · {{ $avance['pendientes'] }} pendientes
                            @endif
                        </p>
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Componente principal --}}
    <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="h-1.5 bg-gradient-to-r from-[#006492] via-sky-500 to-[#88AC2E]"></div>

        {{-- Encabezado --}}
        <div class="border-b border-slate-200 px-4 py-5 dark:border-neutral-800 sm:px-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-sky-50 text-[#006492] dark:bg-sky-950/40 dark:text-sky-300">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M6 12h12M10 19h4"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Filtros de asignación</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Localiza materias y asigna docentes de forma individual o masiva.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if($filtrosActivos > 0)
                        <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-[#006492] ring-1 ring-inset ring-sky-200 dark:bg-sky-950/30 dark:text-sky-300 dark:ring-sky-900">
                            {{ $filtrosActivos }} {{ $filtrosActivos === 1 ? 'filtro activo' : 'filtros activos' }}
                        </span>
                    @endif

                    <flux:button wire:click="limpiarFiltros" variant="ghost" class="cursor-pointer" title="Restablecer filtros">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.7L3 8m0 0h5M3 8V3"/>
                            </svg>
                            <span>Limpiar</span>
                        </div>
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-2">
                    <flux:field>
                        <flux:label>Buscar materia o clave</flux:label>
                        <flux:input
                            type="text"
                            wire:model.live.debounce.350ms="search"
                            placeholder="Ej. Anatomía, LN101…"
                            icon="magnifying-glass"
                            class="w-full"
                        />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Cuatrimestre</flux:label>
                    <flux:select wire:model.live="filtrar_cuatrimestre">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach($cuatrimestres as $cuatrimestre)
                            <flux:select.option value="{{ $cuatrimestre->id }}">
                                {{ $cuatrimestre->nombre_cuatrimestre }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Estado</flux:label>
                    <flux:select wire:model.live="filtrar_asignacion">
                        <flux:select.option value="">Todas</flux:select.option>
                        <flux:select.option value="asignadas">Con profesor</flux:select.option>
                        <flux:select.option value="sin_asignar">Sin profesor</flux:select.option>
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Evaluación</flux:label>
                    <flux:select wire:model.live="filtrar_calificable">
                        <flux:select.option value="">Todas</flux:select.option>
                        <flux:select.option value="true">Calificables</flux:select.option>
                        <flux:select.option value="false">No calificables</flux:select.option>
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Filas</flux:label>
                    <flux:select wire:model.live="por_pagina">
                        <flux:select.option value="10">10</flux:select.option>
                        <flux:select.option value="20">20</flux:select.option>
                        <flux:select.option value="50">50</flux:select.option>
                        <flux:select.option value="100">100</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
                <div class="xl:col-span-2">
                    <flux:field>
                        <flux:label>Profesor asignado</flux:label>
                        <flux:select wire:model.live="filtrar_profesor">
                            <flux:select.option value="">Todos los profesores</flux:select.option>
                            @foreach($profesores as $profesor)
                                <flux:select.option value="{{ $profesor->id }}">
                                    {{ $nombreProfesor($profesor) }}{{ $profesor->status === 'false' ? ' · INACTIVO' : '' }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                <div class="flex items-end">
                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-neutral-800 dark:bg-neutral-950/40 dark:text-slate-300">
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $materias->total() }}</span>
                        {{ $materias->total() === 1 ? 'materia encontrada' : 'materias encontradas' }} con los filtros actuales.
                    </div>
                </div>
            </div>
        </div>

        {{-- Asignación masiva por cuatrimestre --}}
        <div class="border-t border-slate-200 bg-slate-50/70 px-4 py-4 dark:border-neutral-800 dark:bg-neutral-950/30 sm:px-6">
            @if($filtrar_cuatrimestre === '')
                <div class="flex flex-col gap-3 rounded-xl border border-dashed border-sky-200 bg-sky-50/60 p-4 dark:border-sky-900 dark:bg-sky-950/20 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-[#006492] shadow-sm dark:bg-neutral-900 dark:text-sky-300">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M3 12h18"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Asignación masiva por cuatrimestre</p>
                            <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Selecciona un cuatrimestre para habilitar selección múltiple segura.</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-[#006492] dark:text-sky-300">Evita mezclar periodos por accidente</span>
                </div>
            @else
                <div class="grid gap-4 xl:grid-cols-[1fr_auto] xl:items-center">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-white px-3 py-1.5 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-neutral-900 dark:text-slate-200 dark:ring-neutral-700">
                            {{ $totalSeleccionadas }} {{ $totalSeleccionadas === 1 ? 'seleccionada' : 'seleccionadas' }}
                        </span>

                        <button
                            type="button"
                            wire:click="alternarSeleccionPagina"
                            class="cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-sky-300 hover:text-[#006492] dark:border-neutral-700 dark:bg-neutral-900 dark:text-slate-200"
                        >
                            {{ $paginaCompletaSeleccionada ? 'Quitar página' : 'Seleccionar página' }}
                        </button>

                        <button
                            type="button"
                            wire:click="seleccionarTodoCuatrimestre"
                            class="cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-sky-300 hover:text-[#006492] dark:border-neutral-700 dark:bg-neutral-900 dark:text-slate-200"
                        >
                            Todo el cuatrimestre
                        </button>

                        @if($totalSeleccionadas > 0)
                            <button
                                type="button"
                                wire:click="limpiarSeleccionMasiva"
                                class="cursor-pointer rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-950/30"
                            >
                                Limpiar selección
                            </button>
                        @endif
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <button
                            type="button"
                            wire:click="abrirSelectorProfesorMasivo"
                            class="flex min-w-[260px] cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-left shadow-sm transition hover:border-sky-300 dark:border-neutral-700 dark:bg-neutral-900"
                        >
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Profesor para selección</p>
                                <p class="truncate text-sm font-semibold {{ $profesorMasivo ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400' }}">
                                    {{ $profesorMasivo ? $nombreProfesor($profesorMasivo) : 'Buscar profesor…' }}
                                </p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-[#006492]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/>
                            </svg>
                        </button>

                        <flux:button
                            wire:click="prepararAsignacionMasiva"
                            variant="primary"
                            class="cursor-pointer whitespace-nowrap"
                            :disabled="$totalSeleccionadas === 0 || !$profesorMasivo"
                        >
                            Asignar a seleccionadas
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Resultados --}}
        <div class="relative border-t border-slate-200 dark:border-neutral-800">
            <div
                wire:loading.delay
                wire:target="search, filtrar_cuatrimestre, filtrar_asignacion, filtrar_profesor, filtrar_calificable, por_pagina, limpiarFiltros"
                class="absolute inset-0 z-20 grid place-items-center bg-white/70 backdrop-blur-sm dark:bg-neutral-900/70"
                aria-live="polite"
                aria-busy="true"
            >
                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-lg dark:border-neutral-700 dark:bg-neutral-900">
                    <svg class="h-5 w-5 animate-spin text-[#006492]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4Z"></path>
                    </svg>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Aplicando filtros…</span>
                </div>
            </div>

            <div
                class="transition duration-150"
                wire:loading.class="opacity-60"
                wire:target="search, filtrar_cuatrimestre, filtrar_asignacion, filtrar_profesor, filtrar_calificable, por_pagina, limpiarFiltros"
            >
                <div class="flex flex-col gap-2 px-4 py-3 text-sm text-slate-500 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <p>
                        Mostrando
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $materias->firstItem() ?? 0 }}–{{ $materias->lastItem() ?? 0 }}</span>
                        de
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $materias->total() }}</span>
                        resultados
                    </p>
                    <p class="text-xs">Orden: cuatrimestre → orden académico → materia</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-600 dark:bg-neutral-950/60 dark:text-slate-300">
                                <th class="border-y border-r border-slate-200 px-3 py-3 text-center dark:border-neutral-800">
                                    @if($filtrar_cuatrimestre !== '')
                                        <button
                                            type="button"
                                            wire:click="alternarSeleccionPagina"
                                            class="grid h-6 w-6 cursor-pointer place-items-center rounded-md border transition
                                                {{ $paginaCompletaSeleccionada
                                                    ? 'border-[#006492] bg-[#006492] text-white'
                                                    : 'border-slate-300 bg-white text-transparent hover:border-sky-400 dark:border-neutral-600 dark:bg-neutral-900' }}"
                                            title="{{ $paginaCompletaSeleccionada ? 'Quitar selección de esta página' : 'Seleccionar esta página' }}"
                                            aria-label="Seleccionar materias de la página"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                            </svg>
                                        </button>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </th>
                                <th class="sticky left-0 z-10 border-y border-r border-slate-200 bg-slate-50 px-4 py-3 text-center dark:border-neutral-800 dark:bg-neutral-950/60">#</th>
                                <th class="border-y border-r border-slate-200 px-4 py-3 dark:border-neutral-800">Clave</th>
                                <th class="border-y border-r border-slate-200 px-4 py-3 dark:border-neutral-800">Materia</th>
                                <th class="border-y border-r border-slate-200 px-4 py-3 dark:border-neutral-800">Cuatrimestre</th>
                                <th class="border-y border-r border-slate-200 px-4 py-3 text-center dark:border-neutral-800">Estado</th>
                                <th class="border-y border-slate-200 px-4 py-3 dark:border-neutral-800">Profesor asignado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                            @forelse($materias as $key => $materia)
                                @php
                                    $profesorAsignadoId = $profesor_seleccionado[$materia->id] ?? '';
                                    $profesorAsignado = $profesorAsignadoId !== ''
                                        ? $profesores->firstWhere('id', (int) $profesorAsignadoId)
                                        : null;

                                    $color = $profesorAsignado && preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $profesorAsignado->color)
                                        ? $profesorAsignado->color
                                        : '';

                                    $textColor = $color && $esColorClaro($color) ? '#111827' : '#ffffff';
                                    $numeroFila = ($materias->firstItem() ?? 1) + $key;
                                    $estaSeleccionada = $seleccionadas->contains((string) $materia->id);
                                @endphp

                                <tr wire:key="materia-asignacion-{{ $materia->id }}" class="group bg-white transition hover:bg-sky-50/40 dark:bg-neutral-900 dark:hover:bg-sky-950/10">
                                    <td class="border-r border-slate-100 px-3 py-3 text-center dark:border-neutral-800">
                                        @if($filtrar_cuatrimestre !== '')
                                            <input
                                                type="checkbox"
                                                wire:model.live="materias_seleccionadas"
                                                value="{{ $materia->id }}"
                                                class="h-4 w-4 cursor-pointer rounded border-slate-300 text-[#006492] focus:ring-[#006492] dark:border-neutral-600 dark:bg-neutral-800"
                                                aria-label="Seleccionar {{ $materia->nombre }} para asignación masiva"
                                            >
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="sticky left-0 z-10 border-r border-slate-100 bg-white px-4 py-3 text-center font-semibold text-slate-500 group-hover:bg-sky-50/40 dark:border-neutral-800 dark:bg-neutral-900 dark:group-hover:bg-sky-950/10">
                                        {{ $numeroFila }}
                                    </td>
                                    <td class="border-r border-slate-100 px-4 py-3 dark:border-neutral-800">
                                        @if($materia->clave)
                                            <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs font-bold text-slate-700 dark:bg-neutral-800 dark:text-slate-200">
                                                {{ $materia->clave }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="min-w-[300px] border-r border-slate-100 px-4 py-3 dark:border-neutral-800">
                                        <div class="flex items-start gap-2">
                                            <div class="min-w-0">
                                                <div class="font-semibold text-slate-900 dark:text-white">{{ $materia->nombre }}</div>
                                                <div class="mt-1 flex flex-wrap gap-2">
                                                    @if($materia->calificable === 'false')
                                                        <span class="inline-flex rounded-full bg-violet-50 px-2 py-0.5 text-[11px] font-semibold text-violet-700 ring-1 ring-inset ring-violet-200 dark:bg-violet-950/30 dark:text-violet-300 dark:ring-violet-900">
                                                            No calificable
                                                        </span>
                                                    @endif
                                                    @if($estaSeleccionada)
                                                        <span class="inline-flex rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-[#006492] ring-1 ring-inset ring-sky-200 dark:bg-sky-950/30 dark:text-sky-300 dark:ring-sky-900">
                                                            Seleccionada
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap border-r border-slate-100 px-4 py-3 font-medium text-slate-700 dark:border-neutral-800 dark:text-slate-200">
                                        {{ $materia->cuatrimestre?->nombre_cuatrimestre ?? 'Sin cuatrimestre' }}
                                    </td>
                                    <td class="border-r border-slate-100 px-4 py-3 text-center dark:border-neutral-800">
                                        @if($profesorAsignadoId !== '')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-300 dark:ring-emerald-900">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Asignada
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/30 dark:text-amber-300 dark:ring-amber-900">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="min-w-[360px] px-4 py-3">
                                        <button
                                            type="button"
                                            wire:click="abrirSelectorProfesor({{ $materia->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="abrirSelectorProfesor, seleccionarProfesorDesdePicker, seleccionarSinProfesorDesdePicker"
                                            class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl border px-3 py-2.5 text-left text-sm font-medium shadow-sm transition hover:-translate-y-px hover:shadow disabled:cursor-wait disabled:opacity-60 {{ $color ? '' : 'border-slate-300 bg-white text-slate-700 dark:border-neutral-700 dark:bg-neutral-950 dark:text-slate-200' }}"
                                            style="{{ $color ? "background-color: {$color}; color: {$textColor}; border-color: {$color};" : '' }}"
                                            aria-label="Buscar o cambiar profesor de {{ $materia->nombre }}"
                                        >
                                            <div class="min-w-0">
                                                @if($profesorAsignado)
                                                    <p class="truncate font-bold">{{ $nombreProfesor($profesorAsignado) }}</p>
                                                    <p class="mt-0.5 truncate text-[11px] opacity-75">
                                                        {{ $profesorAsignado->perfil ?: 'Profesor asignado' }}
                                                        @if($profesorAsignado->status === 'false')
                                                            · INACTIVO
                                                        @endif
                                                    </p>
                                                @else
                                                    <p class="font-semibold">Buscar profesor…</p>
                                                    <p class="mt-0.5 text-[11px] opacity-70">Escribe nombre, apellido o perfil</p>
                                                @endif
                                            </div>
                                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-16 text-center">
                                        <div class="mx-auto max-w-md">
                                            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-neutral-800 dark:text-slate-500">
                                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 0 0-2 2v14h14v-7M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/>
                                                </svg>
                                            </span>
                                            <h3 class="mt-4 text-base font-bold text-slate-900 dark:text-white">No hay materias que coincidan</h3>
                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Prueba cambiando los filtros o limpiándolos para mostrar nuevamente todas las materias.</p>
                                            <div class="mt-4">
                                                <flux:button wire:click="limpiarFiltros" variant="primary" class="cursor-pointer">
                                                    Limpiar filtros
                                                </flux:button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($materias->hasPages())
                    <div class="border-t border-slate-200 px-4 py-4 dark:border-neutral-800 sm:px-6">
                        {{ $materias->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Selector de profesor con búsqueda (sin depender de Flux Pro) --}}
    <div
        x-cloak
        x-show="selectorAbierto"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-label="Seleccionar profesor"
    >
        <div
            x-show="selectorAbierto"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-neutral-700 dark:bg-neutral-900"
            @click.outside="selectorAbierto = false; buscarProfesor = ''"
        >
            <div class="border-b border-slate-200 px-5 py-4 dark:border-neutral-800">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#006492] dark:text-sky-300">
                            {{ $selector_modo === 'masiva' ? 'Asignación masiva' : 'Asignación individual' }}
                        </p>
                        <h3 class="mt-1 text-lg font-black text-slate-900 dark:text-white">Buscar profesor</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Busca por nombre, apellidos o perfil profesional.</p>
                    </div>
                    <button
                        type="button"
                        @click="selectorAbierto = false; buscarProfesor = ''"
                        class="grid h-9 w-9 cursor-pointer place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-neutral-800 dark:hover:text-white"
                        aria-label="Cerrar selector"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/>
                        </svg>
                    </button>
                </div>

                <div class="relative mt-4">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/>
                    </svg>
                    <input
                        x-ref="buscarProfesor"
                        x-model="buscarProfesor"
                        type="search"
                        placeholder="Ej. Gabriela, Velázquez, Nutrióloga…"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-[#006492] focus:bg-white focus:ring-2 focus:ring-sky-100 dark:border-neutral-700 dark:bg-neutral-950 dark:text-white dark:focus:ring-sky-950"
                    >
                </div>
            </div>

            <div class="overflow-y-auto p-3">
                @if($selector_modo === 'individual')
                    <button
                        type="button"
                        wire:click="seleccionarSinProfesorDesdePicker"
                        class="mb-2 flex w-full cursor-pointer items-center gap-3 rounded-xl border border-dashed border-slate-300 px-4 py-3 text-left transition hover:border-amber-300 hover:bg-amber-50/60 dark:border-neutral-700 dark:hover:border-amber-900 dark:hover:bg-amber-950/20"
                    >
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-neutral-800 dark:text-slate-300">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" d="M5 12h14"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">Dejar sin profesor</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Conserva la asignación y sus registros relacionados; no elimina horarios.</p>
                        </div>
                    </button>
                @endif

                <div class="space-y-1">
                    @foreach($profesores as $profesor)
                        @php
                            $textoBusquedaProfesor = trim(implode(' ', array_filter([
                                $profesor->nombre,
                                $profesor->apellido_paterno,
                                $profesor->apellido_materno,
                                $profesor->perfil,
                            ])));
                            $colorProfesor = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $profesor->color)
                                ? $profesor->color
                                : '#cbd5e1';
                        @endphp

                        <button
                            x-show="coincideProfesor(@js($textoBusquedaProfesor))"
                            x-transition.opacity.duration.100ms
                            type="button"
                            wire:key="picker-profesor-{{ $profesor->id }}"
                            wire:click="seleccionarProfesorDesdePicker({{ $profesor->id }})"
                            @disabled($profesor->status !== 'true')
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition
                                {{ $profesor->status === 'true'
                                    ? 'cursor-pointer hover:bg-sky-50 dark:hover:bg-sky-950/20'
                                    : 'cursor-not-allowed opacity-45' }}"
                        >
                            <span
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-xs font-black shadow-sm ring-1 ring-black/5"
                                style="background-color: {{ $colorProfesor }}; color: {{ $esColorClaro($colorProfesor) ? '#111827' : '#ffffff' }}"
                            >
                                {{ mb_substr($profesor->nombre, 0, 1) }}{{ mb_substr($profesor->apellido_paterno, 0, 1) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $nombreProfesor($profesor) }}</p>
                                    @if($profesor->status !== 'true')
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-neutral-800 dark:text-slate-400">INACTIVO</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">{{ $profesor->perfil ?: 'Sin perfil registrado' }}</p>
                            </div>
                            @if($profesor->status === 'true')
                                <svg class="h-4 w-4 shrink-0 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-slate-200 bg-slate-50 px-5 py-3 text-xs text-slate-500 dark:border-neutral-800 dark:bg-neutral-950/40 dark:text-slate-400">
                Los profesores inactivos se muestran como referencia, pero no pueden recibir nuevas asignaciones.
            </div>
        </div>
    </div>

    {{-- Confirmación de traslapes / impacto académico --}}
    <div
        x-cloak
        x-show="confirmacionAbierta"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-label="Confirmar cambio de profesor"
    >
        <div
            x-show="confirmacionAbierta"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="flex max-h-[88vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-neutral-700 dark:bg-neutral-900"
        >
            <div class="border-b border-slate-200 px-5 py-4 dark:border-neutral-800">
                <div class="flex items-start gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ count($conflictos_pendientes) ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-sky-100 text-[#006492] dark:bg-sky-950/40 dark:text-sky-300' }}">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                            @if(count($conflictos_pendientes))
                                <path stroke-linecap="round" d="M12 8v5m0 3h.01"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.3 3.8 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.8 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"/>
                            @endif
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] {{ count($conflictos_pendientes) ? 'text-amber-600 dark:text-amber-300' : 'text-[#006492] dark:text-sky-300' }}">
                            {{ count($conflictos_pendientes) ? '⚠ Traslape detectado' : 'Confirmación preventiva' }}
                        </p>
                        <h3 class="mt-1 text-lg font-black text-slate-900 dark:text-white">
                            {{ count($conflictos_pendientes) ? 'El docente ya tiene horario que se cruza' : 'Esta materia ya tiene información relacionada' }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Destino:
                            <span class="font-bold text-slate-800 dark:text-slate-200">
                                {{ $profesorDestinoPendiente ? $nombreProfesor($profesorDestinoPendiente) : 'Sin profesor asignado' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="overflow-y-auto p-5">
                @if(count($conflictos_pendientes))
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/20">
                        <p class="text-sm font-bold text-amber-900 dark:text-amber-100">
                            {{ count($conflictos_pendientes) }} {{ count($conflictos_pendientes) === 1 ? 'traslape encontrado' : 'traslapes encontrados' }}
                        </p>
                        <p class="mt-1 text-xs leading-relaxed text-amber-800/80 dark:text-amber-200/80">
                            Se compara la misma modalidad, el mismo día y rangos de hora que se intersecten. Puedes cancelar o confirmar conscientemente la asignación.
                        </p>
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach($conflictos_pendientes as $conflicto)
                            <article class="rounded-xl border border-slate-200 p-4 dark:border-neutral-800">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-black text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                                        ⚠ {{ $conflicto['dia'] }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 dark:bg-neutral-800 dark:text-slate-200">
                                        {{ $conflicto['hora_origen'] }}
                                    </span>
                                    @if($conflicto['hora_origen'] !== $conflicto['hora_conflicto'])
                                        <span class="text-xs text-slate-400">cruza con</span>
                                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">
                                            {{ $conflicto['hora_conflicto'] }}
                                        </span>
                                    @endif
                                    @if(($conflicto['tipo'] ?? '') === 'seleccion')
                                        <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-bold text-violet-700 dark:bg-violet-950/30 dark:text-violet-300">ENTRE SELECCIONADAS</span>
                                    @endif
                                </div>

                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-neutral-950/50">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Materia que quieres asignar</p>
                                        <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $conflicto['materia_origen'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ $conflicto['licenciatura_origen'] }} · Gen. {{ $conflicto['generacion_origen'] }}
                                        </p>
                                    </div>
                                    <div class="rounded-lg bg-rose-50/60 p-3 dark:bg-rose-950/20">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-rose-400">Horario en conflicto</p>
                                        <p class="mt-1 text-sm font-bold text-rose-900 dark:text-rose-100">{{ $conflicto['materia_conflicto'] }}</p>
                                        <p class="mt-1 text-xs text-rose-700/70 dark:text-rose-300/70">
                                            {{ $conflicto['licenciatura_conflicto'] }} · Gen. {{ $conflicto['generacion_conflicto'] }}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                @if(($impacto_pendiente['horarios'] ?? 0) > 0 || ($impacto_pendiente['calificaciones'] ?? 0) > 0 || ($impacto_pendiente['capturas'] ?? 0) > 0)
                    <div class="mt-4 rounded-xl border border-sky-200 bg-sky-50/60 p-4 dark:border-sky-900 dark:bg-sky-950/20">
                        <div class="flex items-start gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-[#006492] shadow-sm dark:bg-neutral-900 dark:text-sky-300">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9h.01M11 12h1v4h1m8-4a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">Registros relacionados que debes considerar</p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                                    El cambio conserva estos registros. No se borran horarios ni calificaciones; únicamente cambia el profesor de la asignación académica.
                                </p>
                                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-5">
                                    <div class="rounded-lg bg-white px-3 py-2 text-center dark:bg-neutral-900">
                                        <p class="text-lg font-black text-slate-900 dark:text-white">{{ $impacto_pendiente['horarios'] ?? 0 }}</p>
                                        <p class="text-[10px] font-bold uppercase text-slate-400">Horarios</p>
                                    </div>
                                    <div class="rounded-lg bg-white px-3 py-2 text-center dark:bg-neutral-900">
                                        <p class="text-lg font-black text-slate-900 dark:text-white">{{ $impacto_pendiente['calificaciones'] ?? 0 }}</p>
                                        <p class="text-[10px] font-bold uppercase text-slate-400">Calificaciones</p>
                                    </div>
                                    <div class="rounded-lg bg-white px-3 py-2 text-center dark:bg-neutral-900">
                                        <p class="text-lg font-black text-slate-900 dark:text-white">{{ $impacto_pendiente['capturas'] ?? 0 }}</p>
                                        <p class="text-[10px] font-bold uppercase text-slate-400">Capturas</p>
                                    </div>
                                    <div class="rounded-lg bg-white px-3 py-2 text-center dark:bg-neutral-900">
                                        <p class="text-lg font-black text-amber-600 dark:text-amber-300">{{ $impacto_pendiente['entregadas'] ?? 0 }}</p>
                                        <p class="text-[10px] font-bold uppercase text-slate-400">Entregadas</p>
                                    </div>
                                    <div class="rounded-lg bg-white px-3 py-2 text-center dark:bg-neutral-900">
                                        <p class="text-lg font-black text-emerald-600 dark:text-emerald-300">{{ $impacto_pendiente['validadas'] ?? 0 }}</p>
                                        <p class="text-[10px] font-bold uppercase text-slate-400">Validadas</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-neutral-800 dark:bg-neutral-950/40 sm:flex-row sm:justify-end">
                <flux:button
                    wire:click="cancelarOperacionPendiente"
                    variant="ghost"
                    class="cursor-pointer"
                >
                    Cancelar
                </flux:button>
                <flux:button
                    wire:click="confirmarOperacionPendiente"
                    variant="primary"
                    class="cursor-pointer"
                >
                    {{ count($conflictos_pendientes) ? 'Asignar de todos modos' : 'Confirmar cambio' }}
                </flux:button>
            </div>
        </div>
    </div>
</div>
