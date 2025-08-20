<!-- listado-pro -->
<div
    x-data="{
        destroyGrupo(id, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `El grupo ${nombre} se eliminará de forma permanente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((r) => r.isConfirmed && @this.call('eliminarGrupo', id))
        }
    }"
    class="space-y-5"
>
    <!-- Encabezado -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Grupos</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Busca, exporta, edita o elimina grupos por licenciatura y cuatrimestre.</p>
    </div>

    <!-- Contenedor listado -->
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow">
        <!-- Acabado superior -->
        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-sky-400 to-indigo-600"></div>

        <!-- Toolbar -->
        <div class="p-4 sm:p-5 lg:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <!-- Buscador -->
                <div class="w-full sm:max-w-xl">
                    <label for="buscar-grupo" class="sr-only">Buscar grupo</label>
                    <flux:input
                        id="buscar-grupo"
                        type="text"
                        wire:model.live="search"
                        placeholder="Buscar por grupo o cuatrimestre…"
                        icon="magnifying-glass"
                        class="w-full"
                    />
                </div>

                <!-- Acciones -->
                <div class="flex items-center gap-2">
                    <div class="hidden sm:flex items-center gap-2 rounded-lg border border-gray-200 dark:border-neutral-800 px-3 py-1.5 bg-gray-50 dark:bg-neutral-800/60">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            Resultados:
                            <strong>{{ method_exists($grupos, 'total') ? $grupos->total() : $grupos->count() }}</strong>
                        </span>
                    </div>

                    @if($grupos->isNotEmpty())
                        <flux:button
                            wire:click="exportarGrupos"
                            variant="primary"
                            class="cursor-pointer bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500"
                            title="Exportar grupos"
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
                </div>
            </div>
        </div>

        <!-- Área de resultados -->
        <div class="px-4 pb-4 sm:px-5 sm:pb-6 lg:px-6">
            <div class="relative">
                <!-- Loader -->
                <div
                    wire:loading.delay
                    wire:target="search, exportarGrupos, eliminarGrupo"
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

                <!-- Contenido con desenfoque mientras carga -->
                <div
                    class="transition filter duration-200"
                    wire:loading.class="blur-sm"
                    wire:target="search, exportarGrupos, eliminarGrupo"
                >
                    <!-- Tabla (desktop) -->
                    <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900">
                        <div class="overflow-x-auto max-h-[65vh]">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 z-10 bg-gray-50/95 dark:bg-neutral-900/95 backdrop-blur text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-neutral-800">
                                    <tr>
                                        <th class="px-4 py-3 text-center font-semibold">#</th>
                                        <th class="px-4 py-3 text-left font-semibold">Licenciatura</th>
                                        <th class="px-4 py-3 text-center font-semibold">Cuatrimestre</th>
                                        <th class="px-4 py-3 text-center font-semibold">Grupo</th>
                                        <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                    @if($grupos->isEmpty())
                                        <tr>
                                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                                <div class="mx-auto w-full max-w-md">
                                                    <div class="rounded-2xl border border-dashed border-gray-300 dark:border-neutral-700 p-6">
                                                        <div class="mb-1 text-base font-semibold">No hay grupos disponibles</div>
                                                        <p class="text-sm">Ajusta tu búsqueda o crea un nuevo grupo.</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        @php
                                            $gruposPorLicenciatura = $grupos->groupBy(fn($g) => optional($g->licenciatura)->nombre ?? '— Sin licenciatura —');
                                        @endphp

                                        @foreach($gruposPorLicenciatura as $licenciaturaNombre => $listaGrupos)
                                            <tr>
                                                <td colspan="5" class="bg-gray-100 dark:bg-neutral-800/70 px-4 py-2">
                                                    <div class="flex items-center gap-2 text-sm font-semibold">
                                                        <span class="text-gray-700 dark:text-gray-200">Licenciatura:</span>
                                                        <span class="inline-flex items-center rounded-full border border-indigo-300/60 bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300 dark:border-indigo-700/50">
                                                            {{ $licenciaturaNombre }}
                                                        </span>
                                                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">Registros: {{ $listaGrupos->count() }}</span>
                                                    </div>
                                                </td>
                                            </tr>

                                            @foreach($listaGrupos as $index => $grupo)
                                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-neutral-800/50">
                                                    <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $index + 1 }}</td>
                                                    <td class="px-4 py-3 text-left text-gray-900 dark:text-white truncate">{{ $licenciaturaNombre }}</td>
                                                    <td class="px-4 py-3 text-center text-gray-900 dark:text-white">
                                                        {{ optional($grupo->cuatrimestre)->cuatrimestre ?? '—' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center text-gray-900 dark:text-white">{{ $grupo->grupo }}</td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <flux:button
                                                                variant="primary"
                                                                class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                                                @click="Livewire.dispatch('abrirGrupo', { id: {{ $grupo->id }} })"
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
                                                                @click="destroyGrupo({{ $grupo->id }}, '{{ $grupo->grupo }}')"
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
                        @if($grupos->isEmpty())
                            <div class="rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-6 text-center">
                                <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">No hay grupos</div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Ajusta tu búsqueda.</p>
                            </div>
                        @else
                            @php
                                $gruposPorLicenciaturaMobile = $grupos->groupBy(fn($g) => optional($g->licenciatura)->nombre ?? '— Sin licenciatura —');
                            @endphp

                            @foreach($gruposPorLicenciaturaMobile as $licNombre => $lista)
                                <div class="px-1 text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Licenciatura:
                                    <span class="inline-flex items-center rounded-full border border-indigo-300/60 bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300 dark:border-indigo-700/50">
                                        {{ $licNombre }}
                                    </span>
                                </div>

                                @foreach($lista as $grupo)
                                    <div class="rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4 shadow-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $grupo->grupo }}</div>
                                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                                    <span class="inline-flex items-center rounded-full border border-sky-300/60 bg-sky-50 px-2 py-0.5 text-[11px] font-medium text-sky-700 dark:bg-sky-900/20 dark:text-sky-300 dark:border-sky-700/50">
                                                        {{ optional($grupo->cuatrimestre)->cuatrimestre ?? '—' }}° Cuatrimestre
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="shrink-0 flex flex-col gap-2">
                                                <flux:button
                                                    variant="primary"
                                                    class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                                    @click="Livewire.dispatch('abrirGrupo', { id: {{ $grupo->id }} })"
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
                                                    @click="destroyGrupo({{ $grupo->id }}, '{{ $grupo->grupo }}')"
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
                <!-- /Contenido con desenfoque -->
            </div>

            <!-- Paginación -->
            <div class="mt-5">
                {{ $grupos->links() }}
            </div>
        </div>
    </div>

    <!-- Modal editar -->
    <livewire:admin.grupo.editar-grupo />
</div>
