<!-- listado-pro -->
<div
    x-data="{
        destroyPeriodo(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Este periodo se eliminará de forma permanente',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((r) => r.isConfirmed && @this.call('eliminarPeriodo', id))
        }
    }"
    class="space-y-5"
>
    <!-- Encabezado -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Periodos</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Filtra, exporta, edita o elimina periodos académicos.</p>
    </div>

    <!-- Contenedor listado -->
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow">
        <!-- Acabado superior -->
        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-sky-400 to-indigo-600"></div>

        <!-- Toolbar -->
        <div class="p-4 sm:p-5 lg:p-6 space-y-4">
            <!-- Filtros -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                <flux:field>
                    <flux:label>Cuatrimestre</flux:label>
                    <flux:select wire:model.live="filtrar_cuatrimestre">
                        <flux:select.option value="">--Selecciona un cuatrimestre--</flux:select.option>
                        @foreach($cuatrimestres as $cuatrimestre)
                            <flux:select.option value="{{ $cuatrimestre->id }}">{{ $cuatrimestre->cuatrimestre }}° Cuatrimestre</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Generación</flux:label>
                    <flux:select wire:model.live="filtrar_generacion">
                        <flux:select.option value="">--Selecciona una generación--</flux:select.option>
                        @foreach($generaciones as $generacion)
                            <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Mes</flux:label>
                    <flux:select wire:model.live="filtrar_mes">
                        <flux:select.option value="">--Selecciona un mes--</flux:select.option>
                        @foreach($meses as $mes)
                            <flux:select.option value="{{ $mes->id }}">{{ $mes->meses }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:input wire:model.live="filtar_inicio_periodo" :label="__('Inicio del periodo')" type="date" autocomplete="fecha_inicio" />
                </flux:field>

                <flux:field>
                    <flux:input wire:model.live="filtar_termino_periodo" :label="__('Término del periodo')" type="date" autocomplete="fecha_termino" />
                </flux:field>
            </div>

            <!-- Buscador + acciones -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="w-full sm:max-w-xl">
                    <flux:input
                        type="text"
                        wire:model.live="search"
                        placeholder="Buscar por ciclo escolar, generación o mes…"
                        icon="magnifying-glass"
                        class="w-full"
                    />
                </div>

                <div class="flex items-center gap-2">
                    @if($periodos->isNotEmpty())
                        <flux:button
                            wire:click="exportarPeriodos"
                            variant="primary"
                            class="cursor-pointer bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500"
                            title="Exportar periodos"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12-3-3m3 3 3-3M4 20h16"/>
                                </svg>
                                <span>Exportar</span>
                            </div>
                        </flux:button>
                    @else
                        <flux:button disabled variant="primary" class="bg-gray-200 text-gray-600 dark:bg-neutral-800 dark:text-gray-400">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12-3-3m3 3 3-3M4 20h16"/>
                                </svg>
                                <span>Exportar</span>
                            </div>
                        </flux:button>
                    @endif

                    <flux:button wire:click="limpiarFiltros" variant="primary" title="Limpiar filtros">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.8 0 5.46.23 8.08.68.54.09.92.56.92 1.1v1.04c0 .42-.17.83-.47 1.13L15.12 12a2.25 2.25 0 0 0-.66 1.59v2.93c0 .83-.48 1.58-1.24 2.01L9.75 21v-6.57c0-.6-.24-1.17-.66-1.59L3.66 7.41A2.25 2.25 0 0 1 3 5.82V4.77c0-.54.38-1.01.92-1.1A48.3 48.3 0 0 1 12 3Z"/>
                            </svg>
                            <span>Limpiar filtros</span>
                        </div>
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Área de resultados -->
        <div class="px-4 pb-4 sm:px-5 sm:pb-6 lg:px-6">
            <div class="relative">
                <!-- Loader -->
                <div
                    wire:loading.delay
                    wire:target="search, filtrar_cuatrimestre, filtrar_generacion, filtrar_mes, filtar_inicio_periodo, filtar_termino_periodo, exportarPeriodos, limpiarFiltros"
                    class="absolute inset-0 z-10 grid place-items-center rounded-xl bg-white/70 dark:bg-neutral-900/70 backdrop-blur"
                    aria-live="polite"
                    aria-busy="true"
                >
                    <div class="flex items-center gap-3 rounded-xl bg-white dark:bg-neutral-900 px-4 py-3 ring-1 ring-gray-200 dark:ring-neutral-800 shadow">
                        <svg class="h-5 w-5 animate-spin text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-200">Cargando…</span>
                    </div>
                </div>

                <!-- WRAPPER que se desenfoca mientras se busca -->
                <div
                    class="transition ease-out duration-200"
                    wire:loading.class="blur-sm opacity-80 pointer-events-none"
                    wire:target="search, filtrar_cuatrimestre, filtrar_generacion, filtrar_mes, filtar_inicio_periodo, filtar_termino_periodo, exportarPeriodos, limpiarFiltros"
                >
                    <!-- Tabla (desktop) -->
                    <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900">
                        <div class="overflow-x-auto max-h-[65vh]">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 z-10 bg-gray-50/95 dark:bg-neutral-900/95 backdrop-blur text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-neutral-800">
                                    <tr>
                                        <th class="px-4 py-3 text-center font-semibold">#</th>
                                        <th class="px-4 py-3 text-center font-semibold">Ciclo escolar</th>
                                        <th class="px-4 py-3 text-center font-semibold">Cuatrimestre</th>
                                        <th class="px-4 py-3 text-center font-semibold">Mes</th>
                                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Inicio del periodo</th>
                                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Término del periodo</th>
                                        <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                    @if($periodos->isEmpty())
                                        <tr>
                                            <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                                <div class="mx-auto w-full max-w-md">
                                                    <div class="rounded-2xl border border-dashed border-gray-300 dark:border-neutral-700 p-6">
                                                        <div class="mb-1 text-base font-semibold">No hay periodos disponibles</div>
                                                        <p class="text-sm">Ajusta tu búsqueda o filtros.</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        @php
                                            $grouped = $periodos->groupBy(fn($item) => $item->generacion->generacion);
                                        @endphp

                                        @foreach($grouped as $generacionNombre => $items)
                                            <tr>
                                                <td colspan="7" class="bg-gray-100 dark:bg-neutral-800/70 px-4 py-2">
                                                    <div class="flex items-center gap-2 text-sm font-semibold">
                                                        <span class="text-gray-700 dark:text-gray-200">Generación:</span>
                                                        <span class="inline-flex items-center rounded-full border border-pink-300/60 bg-pink-50 px-2.5 py-0.5 text-xs font-medium text-pink-700 dark:bg-pink-900/20 dark:text-pink-300 dark:border-pink-700/50">
                                                            {{ $generacionNombre }}
                                                        </span>
                                                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">Registros: {{ $items->count() }}</span>
                                                    </div>
                                                </td>
                                            </tr>

                                            @foreach($items as $k => $periodo)
                                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-neutral-800/50">
                                                    <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $k+1 }}</td>
                                                    <td class="px-4 py-3 text-center text-gray-900 dark:text-white">{{ $periodo->ciclo_escolar }}</td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="inline-flex items-center rounded-full border border-sky-300/60 bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-900/20 dark:text-sky-300 dark:border-sky-700/50">
                                                            {{ $periodo->cuatrimestre->cuatrimestre }}° Cuatrimestre
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center text-gray-900 dark:text-white">{{ $periodo->mes->meses }}</td>
                                                    <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                                        @if($periodo->inicio_periodo)
                                                            {{ \Carbon\Carbon::parse($periodo->inicio_periodo)->format('d-m-Y') }}
                                                        @else
                                                            <span class="inline-flex items-center rounded-full border border-amber-300/60 bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-700/50">
                                                                Fecha pendiente
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                                        @if($periodo->termino_periodo)
                                                            {{ \Carbon\Carbon::parse($periodo->termino_periodo)->format('d-m-Y') }}
                                                        @else
                                                            <span class="inline-flex items-center rounded-full border border-amber-300/60 bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-700/50">
                                                                Fecha pendiente
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <flux:button
                                                                variant="primary"
                                                                class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                                                @click="Livewire.dispatch('abrirPeriodo', { id: {{ $periodo->id }} })"
                                                                title="Editar"
                                                                aria-label="Editar"
                                                            >
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.75 19.9 3 21l1.1-3.75L16.862 4.487Z"/>
                                                                </svg>
                                                            </flux:button>

                                                            <flux:button
                                                                variant="danger"
                                                                class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white"
                                                                @click="destroyPeriodo({{ $periodo->id }})"
                                                                title="Eliminar"
                                                                aria-label="Eliminar"
                                                            >
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-3h4m-6 0a1 1 0 001 1h6a1 1 0 001-1m-8 0V4a2 2 0 012-2h2a2 2 0 012 2v0"/>
                                                                </svg>
                                                            </flux:button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tarjetas (mobile) -->
                    <div class="md:hidden space-y-3">
                        @if($periodos->isEmpty())
                            <div class="rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-6 text-center">
                                <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">No hay periodos</div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Ajusta tu búsqueda o filtros.</p>
                            </div>
                        @else
                            @php
                                $groupedMobile = $periodos->groupBy(fn($item) => $item->generacion->generacion);
                            @endphp

                            @foreach($groupedMobile as $genNombre => $items)
                                <div class="px-1 text-xs font-medium text-gray-600 dark:text-gray-300">Generación:
                                    <span class="inline-flex items-center rounded-full border border-pink-300/60 bg-pink-50 px-2 py-0.5 text-[10px] font-semibold text-pink-700 dark:bg-pink-900/20 dark:text-pink-300 dark:border-pink-700/50">
                                        {{ $genNombre }}
                                    </span>
                                </div>

                                @foreach($items as $periodo)
                                    <div class="rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4 shadow-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                                    <span class="font-mono">{{ $periodo->ciclo_escolar }}</span>
                                                    <span class="inline-flex items-center rounded-full border border-sky-300/60 bg-sky-50 px-2 py-0.5 text-[10px] font-medium text-sky-700 dark:bg-sky-900/20 dark:text-sky-300 dark:border-sky-700/50">
                                                        {{ $periodo->cuatrimestre->cuatrimestre }}° Cuatrimestre
                                                    </span>
                                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $periodo->mes->meses }}</span>
                                                </div>

                                                <div class="mt-2 grid grid-cols-2 gap-2 text-[11px] text-gray-600 dark:text-gray-300">
                                                    <div>
                                                        <span class="block text-gray-500 dark:text-gray-400">Inicio</span>
                                                        <span class="font-medium">
                                                            @if($periodo->inicio_periodo)
                                                                {{ \Carbon\Carbon::parse($periodo->inicio_periodo)->format('d-m-Y') }}
                                                            @else
                                                                Pendiente
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="block text-gray-500 dark:text-gray-400">Término</span>
                                                        <span class="font-medium">
                                                            @if($periodo->termino_periodo)
                                                                {{ \Carbon\Carbon::parse($periodo->termino_periodo)->format('d-m-Y') }}
                                                            @else
                                                                Pendiente
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="shrink-0 flex flex-col gap-2">
                                                <flux:button
                                                    variant="primary"
                                                    class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                                    @click="Livewire.dispatch('abrirPeriodo', { id: {{ $periodo->id }} })"
                                                    title="Editar"
                                                    aria-label="Editar"
                                                >
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.75 19.9 3 21l1.1-3.75L16.862 4.487Z"/>
                                                    </svg>
                                                </flux:button>

                                                <flux:button
                                                    variant="danger"
                                                    class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white"
                                                    @click="destroyPeriodo({{ $periodo->id }})"
                                                    title="Eliminar"
                                                    aria-label="Eliminar"
                                                >
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-3h4m-6 0a1 1 0 001 1h6a1 1 0 001-1m-8 0V4a2 2 0 012-2h2a2 2 0 012 2v0"/>
                                                    </svg>
                                                </flux:button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        @endif
                    </div>
                </div>
                <!-- /WRAPPER -->
            </div>

            <!-- Paginación -->
            <div class="mt-5">
                {{ $periodos->links() }}
            </div>
        </div>
    </div>

    <!-- Modal editar -->
    <livewire:admin.periodo.editar-periodo />
</div>
