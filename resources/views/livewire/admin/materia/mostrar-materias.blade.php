<!-- listado-pro -->
<div
    x-data="{
        destroyMateria(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Esta materia se eliminará de forma permanente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((r) => r.isConfirmed && @this.call('eliminarMateria', id))
        }
    }"
    class="space-y-5"
>
    <!-- Encabezado -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Materias</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Filtra, busca, exporta, edita o elimina materias por licenciatura, cuatrimestre y si es calificable.</p>
    </div>

    <!-- Contenedor listado -->
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow">
        <!-- Acabado superior -->
        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-sky-400 to-indigo-600"></div>

        <!-- Toolbar: filtros + búsqueda + acciones -->
        <div class="p-4 sm:p-5 lg:p-6 space-y-4">
            <!-- Filtros -->
            <div>
                <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                    <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>
                    </svg>
                    Filtrar por
                </h3>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                    <flux:field>
                        <flux:label>Licenciatura</flux:label>
                        <flux:select wire:model.live="filtrar_licenciatura">
                            <flux:select.option value="">--Selecciona una licenciatura---</flux:select.option>
                            @foreach($licenciaturas as $licenciatura)
                                <flux:select.option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Cuatrimestre</flux:label>
                        <flux:select wire:model.live="filtrar_cuatrimestre">
                            <flux:select.option value="">--Selecciona un cuatrimestre---</flux:select.option>
                            @foreach($cuatrimestres as $cuatrimestre)
                                <flux:select.option value="{{ $cuatrimestre->id }}">{{ $cuatrimestre->nombre_cuatrimestre }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Calificable</flux:label>
                        <flux:select wire:model.live="filtrar_calificable">
                            <flux:select.option value="">--Selecciona una opción---</flux:select.option>
                            <flux:select.option value="true">Sí</flux:select.option>
                            <flux:select.option value="false">No</flux:select.option>
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            <!-- Búsqueda + acciones -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <!-- Buscador -->
                <div class="w-full sm:max-w-xl">
                    <label for="buscar-materia" class="sr-only">Buscar Materia</label>
                    <flux:input
                        id="buscar-materia"
                        type="text"
                        wire:model.live="search"
                        placeholder="Buscar por materia o clave…"
                        icon="magnifying-glass"
                        class="w-full"
                    />
                </div>

                <!-- Acciones -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 rounded-lg border border-gray-200 dark:border-neutral-800 px-3 py-1.5 bg-gray-50 dark:bg-neutral-800/60">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            Resultados:
                            <strong>{{ method_exists($materias, 'total') ? $materias->total() : $materias->count() }}</strong>
                        </span>
                    </div>

                    @if($materias->isNotEmpty())
                        <flux:button
                            wire:click="exportarMaterias"
                            variant="primary"
                            class="cursor-pointer bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500"
                            title="Exportar materias"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12-3-3m3 3 3-3M4 20h16"/>
                                </svg>
                                <span>Exportar</span>
                            </div>
                        </flux:button>

                        <flux:button wire:click="limpiarFiltros" variant="primary" class="cursor-pointer">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>
                                </svg>
                                <span>Limpiar filtros</span>
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

                        <flux:button disabled variant="primary" class="bg-gray-200 text-gray-600 dark:bg-neutral-800 dark:text-gray-400">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>
                                </svg>
                                <span>Limpiar filtros</span>
                            </div>
                        </flux:button>
                    @endif
                </div>
            </div>

            <!-- Paginación (top-mobile) -->
            <div class="sm:hidden">
                {{ $materias->links() }}
            </div>
        </div>

        <!-- Área de resultados -->
        <div class="px-4 pb-4 sm:px-5 sm:pb-6 lg:px-6">
            <div class="relative">
                <!-- Loader overlay -->
                <div
                    wire:loading.delay
                    wire:target="search, exportarMaterias, limpiarFiltros, eliminarMateria, filtrar_licenciatura, filtrar_cuatrimestre, filtrar_calificable"
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

                <!-- Contenido con desenfoque mientras busca -->
                <div
                    class="transition filter duration-200"
                    wire:loading.class="blur-sm"
                    wire:target="search, exportarMaterias, limpiarFiltros, eliminarMateria, filtrar_licenciatura, filtrar_cuatrimestre, filtrar_calificable"
                >
                    <!-- Tabla (desktop) -->
                    <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900">
                        <div class="overflow-x-auto max-h-[65vh]">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 z-10 bg-gray-50/95 dark:bg-neutral-900/95 backdrop-blur text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-neutral-800">
                                    <tr>
                                        <th class="px-4 py-3 text-center font-semibold">#</th>
                                        <th class="px-4 py-3 text-left font-semibold">Materia</th>
                                        <th class="px-4 py-3 text-left font-semibold">URL</th>
                                        <th class="px-4 py-3 text-center font-semibold">Clave</th>
                                        <th class="px-4 py-3 text-center font-semibold">Créditos</th>
                                        <th class="px-4 py-3 text-center font-semibold">Cuatrimestre</th>
                                        <th class="px-4 py-3 text-center font-semibold">Calificable</th>
                                        <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                    @if($materias->isEmpty())
                                        <tr>
                                            <td colspan="8" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                                <div class="mx-auto w-full max-w-md">
                                                    <div class="rounded-2xl border border-dashed border-gray-300 dark:border-neutral-700 p-6">
                                                        <div class="mb-1 text-base font-semibold">No hay materias disponibles</div>
                                                        <p class="text-sm">Ajusta tu búsqueda o limpia los filtros.</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        @php
                                            $grouped = $materias->groupBy(fn($item) => $item->licenciatura->nombre);
                                        @endphp

                                        @foreach($grouped as $licenciaturaNombre => $items)
                                            <tr>
                                                <td colspan="8" class="bg-gray-100 dark:bg-neutral-800/70 px-4 py-2">
                                                    <div class="flex items-center gap-2 text-sm font-semibold">
                                                        <span class="text-gray-700 dark:text-gray-200">Licenciatura:</span>
                                                        <span class="inline-flex items-center rounded-full border border-pink-300/60 bg-pink-50 px-2.5 py-0.5 text-xs font-medium text-pink-700 dark:bg-pink-900/20 dark:text-pink-300 dark:border-pink-700/50">
                                                            {{ $licenciaturaNombre }}
                                                        </span>
                                                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">Registros: {{ $items->count() }}</span>
                                                    </div>
                                                </td>
                                            </tr>

                                            @foreach($items as $key => $materia)
                                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-neutral-800/50">
                                                    <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $key+1 }}</td>
                                                    <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $materia->nombre }}</td>
                                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $materia->slug }}</td>
                                                    <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $materia->clave }}</td>
                                                    <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $materia->creditos }}</td>
                                                    <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $materia->cuatrimestre->nombre_cuatrimestre }}</td>
                                                    <td class="px-4 py-3 text-center">
                                                        @if($materia->calificable == "true")
                                                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-300/60 bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-700/50">
                                                                Sí
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 rounded-full border border-rose-300/60 bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700 dark:bg-rose-900/20 dark:text-rose-300 dark:border-rose-700/50">
                                                                No
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <flux:button
                                                                variant="primary"
                                                                class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                                                @click="Livewire.dispatch('abrirMateria', { id: {{ $materia->id }} })"
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
                                                                @click="destroyMateria({{ $materia->id }})"
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
                        @if($materias->isEmpty())
                            <div class="rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-6 text-center">
                                <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">No hay materias</div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Ajusta tu búsqueda o limpia filtros.</p>
                            </div>
                        @else
                            @php
                                $groupedMobile = $materias->groupBy(fn($item) => $item->licenciatura->nombre);
                            @endphp

                            @foreach($groupedMobile as $licNombre => $lista)
                                <div class="px-1 text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Licenciatura:
                                    <span class="inline-flex items-center rounded-full border border-pink-300/60 bg-pink-50 px-2 py-0.5 text-[10px] font-semibold text-pink-700 dark:bg-pink-900/20 dark:text-pink-300 dark:border-pink-700/50">
                                        {{ $licNombre }}
                                    </span>
                                </div>

                                @foreach($lista as $m)
                                    <div class="rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4 shadow-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="font-semibold text-gray-900 dark:text-white truncate">{{ $m->nombre }}</div>
                                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                                    <span class="font-medium">Clave:</span> {{ $m->clave }} ·
                                                    <span class="font-medium">Créditos:</span> {{ $m->creditos }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $m->cuatrimestre->nombre_cuatrimestre }} ·
                                                    @if($m->calificable == "true")
                                                        <span class="inline-flex items-center rounded-full border border-emerald-300/60 bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-700/50">
                                                            Calificable
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-full border border-rose-300/60 bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-700 dark:bg-rose-900/20 dark:text-rose-300 dark:border-rose-700/50">
                                                            No calificable
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 truncate">URL: {{ $m->slug }}</div>
                                            </div>

                                            <div class="shrink-0 flex flex-col gap-2">
                                                <flux:button
                                                    variant="primary"
                                                    class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                                    @click="Livewire.dispatch('abrirMateria', { id: {{ $m->id }} })"
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
                                                    @click="destroyMateria({{ $m->id }})"
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
                {{ $materias->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.materia.editar-materia />
</div>
