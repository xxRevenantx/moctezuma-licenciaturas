<!-- listado-pro -->
<div
    x-data="{
        activarProfesor(cantidad) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Activar ${cantidad} profesor(es).`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((r) => r.isConfirmed && $wire.activarprofesoresSeleccionados())
        },
        inactivarProfesor(cantidad) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Inactivar ${cantidad} profesor(es).`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((r) => r.isConfirmed && $wire.inactivarprofesoresSeleccionados())
        }
    }"
    class="space-y-5"
>
    <!-- Encabezado -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Profesores</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Busca, filtra, exporta y edita profesores. Selecciona varios para activar/inactivar en bloque.
        </p>
    </div>

    <!-- Contenedor listado -->
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow">
        <!-- Acabado superior -->
        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-sky-400 to-indigo-600"></div>

        <!-- Toolbar -->
        <div class="p-4 sm:p-5 lg:p-6 space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
                <!-- Filtro Status -->
                <div class="lg:col-span-3">
                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model.live="filtrar_status">
                            <flux:select.option value="">--Selecciona una opción---</flux:select.option>
                            <flux:select.option value="Activo">Activo</flux:select.option>
                            <flux:select.option value="Inactivo">Inactivo</flux:select.option>
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Buscador -->
                <div class="lg:col-span-6">
                    <flux:label>Buscar profesor</flux:label>
                    <flux:input
                        id="buscar-profesor"
                        type="text"
                        wire:model.live="search"
                        placeholder="Buscar por nombre, CURP, email o perfil…"
                        icon="magnifying-glass"
                        class="w-full"
                    />
                </div>

                <!-- Acciones rápidas -->
                <div class="lg:col-span-3 flex items-center justify-start lg:justify-end gap-2">
                    @if($profesores->isNotEmpty())
                        <flux:button
                            wire:click="exportarProfesores"
                            variant="primary"
                            class="cursor-pointer bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500"
                            title="Exportar profesores"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12-3-3m3 3 3-3M4 20h16"/>
                                </svg>
                                <span>Exportar</span>
                            </div>
                        </flux:button>

                        <flux:button wire:click="limpiarFiltros" variant="primary" class="cursor-pointer" title="Limpiar filtros">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>
                                </svg>
                                <span>Limpiar</span>
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
                                <span>Limpiar</span>
                            </div>
                        </flux:button>
                    @endif
                </div>
            </div>

            <!-- Selección y acciones en bloque -->
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="hidden sm:flex items-center gap-2 rounded-lg border border-gray-200 dark:border-neutral-800 px-3 py-1.5 bg-gray-50 dark:bg-neutral-800/60">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                        Resultados:
                        <strong>{{ method_exists($profesores, 'total') ? $profesores->total() : $profesores->count() }}</strong>
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    @if(count($selected) > 0)
                        <span class="text-sm text-gray-700 dark:text-gray-200">
                            Seleccionados: <strong>{{ count($selected) }}</strong>
                        </span>

                        <flux:button
                            variant="primary"
                            class="cursor-pointer bg-emerald-600 hover:bg-emerald-700"
                            @click="activarProfesor({{ count($selected) }})"
                            title="Activar seleccionados"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12l2 2 4-4 1.5 1.5-5.5 5.5L7.5 13.5z"/></svg>
                                Activar
                            </div>
                        </flux:button>

                        <flux:button
                            variant="danger"
                            class="cursor-pointer bg-rose-600 hover:bg-rose-700"
                            @click="inactivarProfesor({{ count($selected) }})"
                            title="Inactivar seleccionados"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                Inactivar
                            </div>
                        </flux:button>
                    @else
                        <span class="text-sm text-gray-500 dark:text-gray-400">Selecciona filas para acciones en bloque</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Área de resultados -->
        <div class="px-4 pb-4 sm:px-5 sm:pb-6 lg:px-6">
            <div class="relative">
                <!-- Loader Overlay -->
                <div
                    wire:loading.delay
                    wire:target="search, filtrar_status, exportarProfesores, limpiarFiltros, activarprofesoresSeleccionados, inactivarprofesoresSeleccionados"
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
                    wire:target="search, filtrar_status, exportarProfesores, limpiarFiltros, activarprofesoresSeleccionados, inactivarprofesoresSeleccionados"
                >
                    @if ($profesores->count())
                        <!-- Tabla (desktop) -->
                        <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900">
                            <div class="overflow-x-auto max-h-[70vh]">
                                <table class="min-w-full text-sm" wire:key="tabla-profesores">
                                    <thead class="sticky top-0 z-10 bg-gray-50/95 dark:bg-neutral-900/95 backdrop-blur text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-neutral-800">
                                        <tr>
                                            <th class="px-4 py-3 text-center font-semibold">
                                                <input type="checkbox" wire:model.live="selectAll" aria-label="Seleccionar todos" />
                                            </th>
                                            <th class="px-4 py-3 text-center font-semibold">#</th>
                                            <th class="px-4 py-3 text-left font-semibold">Foto</th>
                                            <th class="px-4 py-3 text-center font-semibold">Color</th>
                                            <th class="px-4 py-3 text-left font-semibold">Nombre</th>
                                            <th class="px-4 py-3 text-center font-semibold">CURP</th>
                                            <th class="px-4 py-3 text-left font-semibold">Email</th>
                                            <th class="px-4 py-3 text-left font-semibold">Perfil</th>
                                            <th class="px-4 py-3 text-center font-semibold">Teléfono</th>
                                            <th class="px-4 py-3 text-center font-semibold">Status</th>
                                            <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                        @foreach ($profesores as $key => $profesor)
                                            <tr wire:key="row-{{ $profesor->id }}" class="transition-colors hover:bg-gray-50/70 dark:hover:bg-neutral-800/50 {{ in_array($profesor->id, $selected) ? 'bg-blue-50 dark:bg-[#0b2940]/60' : '' }}">
                                                <td class="px-4 py-3 text-center">
                                                    <input type="checkbox" wire:model.live="selected" value="{{ $profesor->id }}" aria-label="Seleccionar profesor {{ $profesor->id }}" />
                                                </td>
                                                <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $key + 1 }}</td>
                                                <td class="px-4 py-3">
                                                    @if ($profesor->foto)
                                                        <img src="{{ asset('storage/profesores/' . $profesor->foto) }}" alt="Foto de {{ $profesor->nombre }}" class="w-12 h-12 rounded-full object-cover">
                                                    @else
                                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <div class="w-5 h-5 rounded-full border border-gray-300 mx-auto" style="background-color: {{ $profesor->color ?? '#e5e7eb' }};"></div>
                                                </td>
                                                <td class="px-4 py-3 text-gray-900 dark:text-white">
                                                    {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }} {{ $profesor->nombre }}
                                                </td>
                                                <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $profesor->user->CURP }}</td>
                                                <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $profesor->user->email }}</td>
                                                <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $profesor->perfil }}</td>
                                                <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $profesor->telefono }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    @if ($profesor->user->status == 'true')
                                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-300/60 bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-700/50">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                            Activo
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded-full border border-rose-300/60 bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700 dark:bg-rose-900/20 dark:text-rose-300 dark:border-rose-700/50">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                            Inactivo
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center justify-center">
                                                        <flux:button
                                                            variant="primary"
                                                            class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                                            @click="Livewire.dispatch('abrirProfesor', { id: {{ $profesor->id }} })"
                                                            title="Editar"
                                                            aria-label="Editar"
                                                        >
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.75 19.9 3 21l1.1-3.75L16.862 4.487Z"/>
                                                            </svg>
                                                        </flux:button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación desktop -->
                            <div class="mt-4">
                                {{ $profesores->links() }}
                            </div>
                        </div>

                        <!-- Tarjetas (mobile) -->
                        <div class="md:hidden space-y-3">
                            @foreach ($profesores as $key => $p)
                                <div class="rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4 shadow-sm">
                                    <div class="flex items-start gap-3">
                                        <div class="shrink-0">
                                            @if ($p->foto)
                                                <img src="{{ asset('storage/profesores/' . $p->foto) }}" alt="Foto de {{ $p->nombre }}" class="w-12 h-12 rounded-full object-cover">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                    #{{ $key + 1 }} · {{ $p->apellido_paterno }} {{ $p->apellido_materno }} {{ $p->nombre }}
                                                </div>
                                                <input type="checkbox" wire:model.live="selected" value="{{ $p->id }}" aria-label="Seleccionar profesor {{ $p->id }}" />
                                            </div>

                                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                                <span class="font-medium">CURP:</span> {{ $p->user->CURP }}
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-300">
                                                <span class="font-medium">Email:</span> {{ $p->user->email }}
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-300">
                                                <span class="font-medium">Perfil:</span> {{ $p->perfil }} ·
                                                <span class="font-medium">Tel:</span> {{ $p->telefono }}
                                            </div>

                                            <div class="mt-2 flex items-center justify-between">
                                                @if ($p->user->status == 'true')
                                                    <span class="inline-flex items-center gap-1 rounded-full border border-emerald-300/60 bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-700/50">
                                                        Activo
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full border border-rose-300/60 bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-700 dark:bg-rose-900/20 dark:text-rose-300 dark:border-rose-700/50">
                                                        Inactivo
                                                    </span>
                                                @endif

                                                <flux:button
                                                    variant="primary"
                                                    class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                                    @click="Livewire.dispatch('abrirProfesor', { id: {{ $p->id }} })"
                                                    title="Editar"
                                                    aria-label="Editar"
                                                >
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.75 19.9 3 21l1.1-3.75L16.862 4.487Z"/>
                                                    </svg>
                                                </flux:button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Paginación móvil -->
                            <div class="mt-4">
                                {{ $profesores->links() }}
                            </div>
                        </div>
                    @else
                        <!-- Vacío -->
                        <div class="rounded-2xl border border-dashed border-gray-300 dark:border-neutral-700 p-8 text-center">
                            <div class="mb-1 text-base font-semibold text-gray-800 dark:text-gray-200">No se encontraron profesores</div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Ajusta tu búsqueda o cambia los filtros.</p>
                        </div>
                    @endif
                </div>
                <!-- /Contenido con desenfoque -->
            </div>
        </div>
    </div>

    <!-- MONTA EL MODAL FUERA DEL CONTEXTO DEL LISTADO -->
   {{-- AISLAR EL MODAL DEL PADRE --}}
<div wire:ignore>
    <livewire:admin.profesor.editar-profesor :wire:key="'editar-profesor-modal'" />
</div>

</div>
