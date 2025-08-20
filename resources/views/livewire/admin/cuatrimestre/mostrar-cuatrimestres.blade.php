<!-- listado-pro -->
<div
    x-data="{
        destroyCuatrimestre(id, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `El ${nombre}° Cuatrimestre se eliminará de forma permanente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((r) => r.isConfirmed && @this.call('eliminarCuatrimestre', id))
        },
        confirmarEliminacionSeleccionados(selected) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Se eliminarán ${selected} cuatrimestres de forma permanente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((r) => r.isConfirmed && @this.call('eliminarCuatrimestreSeleccionados'))
        }
    }"
    class="space-y-5"
>
    <!-- Encabezado -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Cuatrimestres</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Busca, edita o elimina cuatrimestres y gestiona acciones masivas.</p>
    </div>

    <!-- Contenedor listado -->
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow">
        <!-- Acabado superior -->
        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-sky-400 to-indigo-600"></div>

        <!-- Toolbar -->
        <div class="p-4 sm:p-5 lg:p-6 space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <!-- Buscador -->
                <div class="w-full sm:max-w-xl">
                    <label for="buscar-cuatrimestre" class="sr-only">Buscar cuatrimestre</label>
                    <flux:input
                        id="buscar-cuatrimestre"
                        type="text"
                        wire:model.live="search"
                        placeholder="Buscar por número, nombre o meses…"
                        icon="magnifying-glass"
                        class="w-full"
                    />
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Resultados:
                        <strong>{{ method_exists($cuatrimestres, 'total') ? $cuatrimestres->total() : $cuatrimestres->count() }}</strong>
                    </div>
                </div>

                <!-- Acciones masivas -->
                <div class="flex items-center gap-2">
                    @if(count($selected) > 0)
                        <flux:button
                            variant="danger"
                            class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white"
                            @click="confirmarEliminacionSeleccionados({{ count($selected) }})"
                            title="Eliminar seleccionados"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-3h4m-6 0a1 1 0 001 1h6a1 1 0 001-1m-8 0V4a2 2 0 012-2h2a2 2 0 012 2v0"/>
                                </svg>
                                Eliminar seleccionados ({{ count($selected) }})
                            </div>
                        </flux:button>
                    @else
                        <flux:button disabled variant="primary" class="bg-gray-200 text-gray-600 dark:bg-neutral-800 dark:text-gray-400">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-3h4m-6 0a1 1 0 001 1h6a1 1 0 001-1m-8 0V4a2 2 0 012-2h2a2 2 0 012 2v0"/>
                                </svg>
                                Eliminar seleccionados
                            </div>
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Área de resultados -->
        <div class="px-4 pb-4 sm:px-5 sm:pb-6 lg:px-6">
            <div class="relative">
                <!-- Loader overlay -->
                <div
                    wire:loading.delay
                    wire:target="search, eliminarCuatrimestre, eliminarCuatrimestreSeleccionados, selected, selectAll"
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
                    wire:target="search, eliminarCuatrimestre, eliminarCuatrimestreSeleccionados, selected, selectAll"
                >
                    <!-- Tabla (desktop) -->
                    <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900">
                        <div class="overflow-x-auto max-h-[70vh]">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 z-10 bg-gray-50/95 dark:bg-neutral-900/95 backdrop-blur text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-neutral-800">
                                    <tr>
                                        <th class="px-4 py-3 text-center font-semibold">
                                            <input type="checkbox" wire:model.live="selectAll" aria-label="Seleccionar todos" />
                                        </th>
                                        <th class="px-4 py-3 text-center font-semibold">ID</th>
                                        <th class="px-4 py-3 text-center font-semibold">Cuatrimestre</th>
                                        <th class="px-4 py-3 text-left font-semibold">Nombre cuatrimestre</th>
                                        <th class="px-4 py-3 text-center font-semibold">Meses</th>
                                        <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                    @forelse($cuatrimestres as $cuatrimestre)
                                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-neutral-800/50 {{ in_array($cuatrimestre->id, $selected) ? 'bg-blue-50 dark:bg-[#0b3a55]/40' : '' }}">
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" wire:model.live="selected" value="{{ $cuatrimestre->id }}" aria-label="Seleccionar cuatrimestre {{ $cuatrimestre->id }}" />
                                            </td>
                                            <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $cuatrimestre->id }}</td>
                                            <td class="px-4 py-3 text-center text-gray-900 dark:text-white">{{ $cuatrimestre->cuatrimestre }}</td>
                                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $cuatrimestre->nombre_cuatrimestre }}</td>
                                            <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">
                                                {{ optional($cuatrimestre->mes)->meses ?? 'Sin asignar' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-center gap-2">
                                                    <flux:button
                                                        variant="primary"
                                                        class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                                        @click="Livewire.dispatch('abrirCuatrimestre', { id: {{ $cuatrimestre->id }} })"
                                                        title="Editar"
                                                        aria-label="Editar cuatrimestre"
                                                    >
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.75 19.9 3 21l1.1-3.75L16.862 4.487Z"/>
                                                        </svg>
                                                    </flux:button>

                                                    <flux:button
                                                        variant="danger"
                                                        class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white"
                                                        @click="destroyCuatrimestre({{ $cuatrimestre->id }}, '{{ $cuatrimestre->cuatrimestre }}')"
                                                        title="Eliminar"
                                                        aria-label="Eliminar cuatrimestre"
                                                    >
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-3h4m-6 0a1 1 0 001 1h6a1 1 0 001-1m-8 0V4a2 2 0 012-2h2a2 2 0 012 2v0"/>
                                                        </svg>
                                                    </flux:button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                                <div class="mx-auto w-full max-w-md">
                                                    <div class="rounded-2xl border border-dashed border-gray-300 dark:border-neutral-700 p-6">
                                                        <div class="mb-1 text-base font-semibold">No hay cuatrimestres disponibles</div>
                                                        <p class="text-sm">Ajusta tu búsqueda o crea un nuevo registro.</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tarjetas (mobile) -->
                    <div class="md:hidden space-y-3">
                        @forelse($cuatrimestres as $c)
                            <div class="rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <input type="checkbox" wire:model.live="selected" value="{{ $c->id }}" aria-label="Seleccionar cuatrimestre {{ $c->id }}" />
                                            <span>#{{ $c->id }}</span>
                                            <span class="inline-flex items-center rounded-full border border-sky-300/60 bg-sky-50 px-2 py-0.5 text-[10px] font-medium text-sky-700 dark:bg-sky-900/20 dark:text-sky-300 dark:border-sky-700/50">
                                                {{ $c->cuatrimestre }}° Cuatrimestre
                                            </span>
                                        </div>
                                        <div class="mt-1 font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $c->nombre_cuatrimestre }}
                                        </div>
                                        <div class="text-xs text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Meses:</span> {{ optional($c->mes)->meses ?? 'Sin asignar' }}
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex flex-col gap-2">
                                        <flux:button
                                            variant="primary"
                                            class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                            @click="Livewire.dispatch('abrirCuatrimestre', { id: {{ $c->id }} })"
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
                                            @click="destroyCuatrimestre({{ $c->id }}, '{{ $c->cuatrimestre }}')"
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
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-6 text-center">
                                <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">No hay cuatrimestres</div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Ajusta tu búsqueda.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <!-- /WRAPPER -->
            </div>

            <!-- Paginación -->
            <div class="mt-5">
                {{ $cuatrimestres->links() }}
            </div>

            <!-- Resumen selección -->
            <div class="mt-3 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                Cuatrimestres seleccionados: <strong>{{ count($selected) }}</strong>
            </div>
        </div>
    </div>

    <!-- Modal editar -->
    <livewire:admin.cuatrimestre.editar-cuatrimestre />
</div>
