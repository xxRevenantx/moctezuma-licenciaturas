<div
    x-data ="{
        destroyLicenciatura(id, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `La Licenciatura en ${nombre} se eliminará de forma permanente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarLicenciatura', id);
                }
            })
        }
    }"
    class="space-y-4"
>
    <!-- Header -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Buscar Licenciatura</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Administra, exporta y edita las licenciaturas registradas.</p>
    </div>

    <!-- Toolbar -->
    <div
        class="flex flex-col md:flex-row md:items-center gap-3 md:gap-4 rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white/70 dark:bg-neutral-900/60 p-3 md:p-4 shadow-sm"
    >
        <div class="w-full md:max-w-xl">
            <label for="buscar-lic" class="sr-only">Buscar Licenciatura</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none">
                        <path stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
                              d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                    </svg>
                </div>
                <flux:input
                    id="buscar-lic"
                    type="text"
                    wire:model.live="search"
                    placeholder="Buscar por nombre, nombre corto o RVOE…"
                    class="pl-10 w-full"
                />
            </div>
        </div>

        <div class="flex items-center gap-2 md:ml-auto">
            @if($licenciaturas->isNotEmpty())
                <flux:button
                    wire:click="exportarLicenciaturas"
                    variant="primary"
                    class="cursor-pointer bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500"
                >
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 16V4m0 12l-3-3m3 3 3-3M4 20h16"/>
                        </svg>
                        <span>Exportar</span>
                    </div>
                </flux:button>
            @else
                <flux:button
                    disabled
                    variant="primary"
                    class="bg-gray-200 text-gray-600 dark:bg-neutral-800 dark:text-gray-400"
                >
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 16V4m0 12l-3-3m3 3 3-3M4 20h16"/>
                        </svg>
                        <span>Exportar</span>
                    </div>
                </flux:button>
            @endif
        </div>
    </div>

    <!-- List / Table container -->
    <div class="relative">
        <!-- Loader -->
        <div
            wire:loading.delay
            wire:target="search, exportarLicenciaturas"
            class="absolute inset-0 z-10 grid place-items-center rounded-2xl bg-white/60 dark:bg-neutral-900/60"
        >
            <div class="flex items-center gap-3 rounded-xl bg-white dark:bg-neutral-900 px-4 py-3 ring-1 ring-gray-200 dark:ring-neutral-800 shadow">
                <svg class="h-5 w-5 animate-spin text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <span class="text-sm text-gray-700 dark:text-gray-200">Cargando…</span>
            </div>
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-hidden rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-neutral-800/70 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 text-center font-semibold">#</th>
                            <th class="px-4 py-3 text-center font-semibold">Imagen</th>
                            <th class="px-4 py-3 text-left font-semibold">Licenciatura</th>
                            <th class="px-4 py-3 text-left font-semibold">Nombre corto</th>
                            <th class="px-4 py-3 text-left font-semibold">RVOE</th>
                            <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                        @if($licenciaturas->isEmpty())
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                    <div class="mx-auto w-full max-w-md">
                                        <div class="rounded-2xl border border-dashed border-gray-300 dark:border-neutral-700 p-6">
                                            <div class="mb-2 font-semibold">No hay licenciaturas</div>
                                            <p class="text-sm">Intenta ajustar tu búsqueda o crea una nueva licenciatura.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @else
                            @foreach($licenciaturas as $key => $licenciatura)
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-neutral-800/40">
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-200">{{ $key+1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center">
                                            @if ($licenciatura->imagen)
                                                <img src="{{ asset('storage/licenciaturas/' . $licenciatura->imagen) }}"
                                                     alt="{{ $licenciatura->nombre }}"
                                                     class="h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-neutral-700">
                                            @else
                                                <img src="{{ asset('storage/logo-moctezuma.jpg') }}"
                                                     alt="Default"
                                                     class="h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-neutral-700">
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $licenciatura->nombre }}</div>
                                        @if($licenciatura->slug)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">/{{ $licenciatura->slug }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $licenciatura->nombre_corto }}</td>
                                    <td class="px-4 py-3">
                                        @if($licenciatura->RVOE)
                                            <span class="inline-flex items-center rounded-full border border-emerald-300/60 bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-700/50">
                                                RVOE: {{ $licenciatura->RVOE }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full border border-gray-300/70 bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-neutral-800/60 dark:text-gray-300 dark:border-neutral-700">
                                                Sin RVOE
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <flux:button
                                                variant="primary"
                                                class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                                @click="Livewire.dispatch('abrirModal', { id: {{ $licenciatura->id }} })"
                                            >
                                                <div class="flex items-center gap-1">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.75 19.9 3 21l1.1-3.75L16.862 4.487Z"/>
                                                    </svg>

                                                </div>
                                            </flux:button>

                                            <flux:button
                                                variant="danger"
                                                class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white"
                                                @click="destroyLicenciatura({{ $licenciatura->id }}, '{{ $licenciatura->nombre }}')"
                                            >
                                                <div class="flex items-center gap-1">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-3h4m-6 0a1 1 0 001 1h6a1 1 0 001-1m-8 0V4a2 2 0 012-2h2a2 2 0 012 2v0"/>
                                                    </svg>

                                                </div>
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile cards -->
        <div class="md:hidden space-y-3">
            @if($licenciaturas->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-300 dark:border-neutral-700 p-6 text-center">
                    <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">No hay licenciaturas</div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ajusta tu búsqueda o crea una nueva.</p>
                </div>
            @else
                @foreach($licenciaturas as $key => $licenciatura)
                    <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="shrink-0">
                                @if ($licenciatura->imagen)
                                    <img src="{{ asset('storage/licenciaturas/' . $licenciatura->imagen) }}"
                                         alt="{{ $licenciatura->nombre }}"
                                         class="h-12 w-12 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-neutral-700">
                                @else
                                    <img src="{{ asset('storage/logo-moctezuma.jpg') }}"
                                         alt="Default"
                                         class="h-12 w-12 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-neutral-700">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">#{{ $key+1 }}</span>
                                    @if($licenciatura->RVOE)
                                        <span class="ml-2 inline-flex items-center rounded-full border border-emerald-300/60 bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-700/50">
                                            RVOE: {{ $licenciatura->RVOE }}
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-1 font-semibold text-gray-900 dark:text-white truncate">{{ $licenciatura->nombre }}</div>
                                @if($licenciatura->nombre_corto)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Corto: {{ $licenciatura->nombre_corto }}</div>
                                @endif
                                @if($licenciatura->slug)
                                    <div class="text-xs text-gray-400 dark:text-gray-500">/{{ $licenciatura->slug }}</div>
                                @endif

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <flux:button
                                        variant="primary"
                                        class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                        @click="Livewire.dispatch('abrirModal', { id: {{ $licenciatura->id }} })"
                                    >
                                        <div class="flex items-center gap-1">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.75 19.9 3 21l1.1-3.75L16.862 4.487Z"/>
                                            </svg>

                                        </div>
                                    </flux:button>

                                    <flux:button
                                        variant="danger"
                                        class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white"
                                        @click="destroyLicenciatura({{ $licenciatura->id }}, '{{ $licenciatura->nombre }}')"
                                    >
                                        <div class="flex items-center gap-1">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-3h4m-6 0a1 1 0 001 1h6a1 1 0 001-1m-8 0V4a2 2 0 012-2h2a2 2 0 012 2v0"/>
                                            </svg>

                                        </div>
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $licenciaturas->links() }}
    </div>

    <!-- Modal editar -->
    <livewire:admin.licenciatura.editar-licenciatura />
</div>
