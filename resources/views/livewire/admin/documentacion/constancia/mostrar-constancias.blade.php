<div
    x-data ="{
        destroyConstancia(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'La constancia se eliminará de forma permanente',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarConstancia', id);
                }
            })
        }
    }"
    class="space-y-5"
>
    <!-- Encabezado -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Constancias</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Consulta, descarga, edita o elimina constancias emitidas.</p>
    </div>

    <!-- Card contenedor (listado-pro) -->
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow">
        <!-- Top accent -->
        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-sky-400 to-indigo-600"></div>

        <!-- Toolbar -->
        <div class="p-4 sm:p-5 lg:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="w-full sm:max-w-md">
                    <label for="buscar-const" class="sr-only">Buscar constancia</label>
                    <flux:input
                        id="buscar-const"
                        type="text"
                        wire:model.live="search"
                        placeholder="Buscar por folio, matrícula o alumno…"
                        icon="magnifying-glass"
                        class="w-full"
                    />
                </div>

                <!-- Resumen / contador -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 rounded-lg border border-gray-200 dark:border-neutral-800 px-3 py-1.5 bg-gray-50 dark:bg-neutral-800/60">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            Resultados:
                            <strong>{{ method_exists($constancias, 'total') ? $constancias->total() : $constancias->count() }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 pb-4 sm:px-5 sm:pb-6 lg:px-6">
            <div class="relative">
                <!-- Loader -->
                <div
                    wire:loading.delay
                    wire:target="search, eliminarConstancia"
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

                <!-- Tabla (desktop) -->
                <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900">
                    <div class="overflow-x-auto max-h-[65vh]">
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0 z-10 bg-gray-50/95 dark:bg-neutral-900/95 backdrop-blur text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-neutral-800">
                                <tr>
                                    <th class="px-4 py-3 text-center font-semibold">ID</th>
                                    <th class="px-4 py-3 text-center font-semibold">Folio</th>
                                    <th class="px-4 py-3 text-center font-semibold">Matrícula</th>
                                    <th class="px-4 py-3 text-left font-semibold">Nombre del alumno</th>
                                    <th class="px-4 py-3 text-center font-semibold">Tipo de constancia</th>
                                    <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Fecha de expedición</th>
                                    <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                @if($constancias->isEmpty())
                                    <tr>
                                        <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                            <div class="mx-auto w-full max-w-md">
                                                <div class="rounded-2xl border border-dashed border-gray-300 dark:border-neutral-700 p-6">
                                                    <div class="mb-1 text-base font-semibold">No hay constancias disponibles</div>
                                                    <p class="text-sm">Ajusta tu búsqueda o revisa los filtros.</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    @foreach($constancias as $key => $constancia)
                                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-neutral-800/50">
                                            <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $key+1 }}</td>
                                            <td class="px-4 py-3 text-center font-mono text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ str_pad($constancia->no_constancia, 2, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td class="px-4 py-3 text-center font-mono text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                                {{ $constancia->alumno->matricula }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-900 dark:text-white">
                                                <div class="font-medium truncate">
                                                    {{ $constancia->alumno->nombre }} {{ $constancia->alumno->apellido_paterno }} {{ $constancia->alumno->apellido_materno }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($constancia->tipo_constancia == 1)
                                                    <span class="inline-flex items-center gap-1 rounded-full border border-sky-300/60 bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-900/20 dark:text-sky-300 dark:border-sky-700/50">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                                        Estudios
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full border border-amber-300/60 bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-700/50">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                        Rel. Exteriores
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($constancia->fecha_expedicion)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-center gap-2">
                                                    <form action="{{ route('admin.pdf.documentacion.constancia') }}" method="GET" target="_blank">
                                                        <input type="hidden" name="constancia_id" value="{{ $constancia->id }}">
                                                        <flux:button
                                                            variant="primary"
                                                            type="submit"
                                                            class="cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white focus-visible:ring-2 focus-visible:ring-indigo-500"
                                                            title="Descargar PDF"
                                                            aria-label="Descargar PDF"
                                                            wire:loading.attr="disabled"
                                                        >
                                                            <flux:icon.download />
                                                        </flux:button>
                                                    </form>

                                                    <flux:button
                                                        variant="primary"
                                                        class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white focus-visible:ring-2 focus-visible:ring-amber-500"
                                                        @click="Livewire.dispatch('abrirConstancia', { id: {{ $constancia->id }} })"
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
                                                        class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white focus-visible:ring-2 focus-visible:ring-rose-500"
                                                        @click="destroyConstancia({{ $constancia->id }})"
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
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tarjetas (mobile) -->
                <div class="md:hidden space-y-3">
                    @if($constancias->isEmpty())
                        <div class="rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-6 text-center">
                            <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">No hay constancias</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Ajusta tu búsqueda.</p>
                        </div>
                    @else
                        @foreach($constancias as $key => $constancia)
                            <div class="rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <span>#{{ $key+1 }}</span>
                                            <span class="font-mono">Folio: {{ str_pad($constancia->no_constancia, 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="font-mono">Mat: {{ $constancia->alumno->matricula }}</span>
                                        </div>
                                        <div class="mt-1 font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $constancia->alumno->nombre }} {{ $constancia->alumno->apellido_paterno }} {{ $constancia->alumno->apellido_materno }}
                                        </div>
                                        <div class="mt-2">
                                            @if ($constancia->tipo_constancia == 1)
                                                <span class="inline-flex items-center gap-1 rounded-full border border-sky-300/60 bg-sky-50 px-2 py-0.5 text-[11px] font-medium text-sky-700 dark:bg-sky-900/20 dark:text-sky-300 dark:border-sky-700/50">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                                    Estudios
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full border border-amber-300/60 bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-700/50">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                    Rel. Exteriores
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Fecha: {{ \Carbon\Carbon::parse($constancia->fecha_expedicion)->format('d/m/Y') }}
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex flex-col gap-2">
                                        <form action="{{ route('admin.pdf.documentacion.constancia') }}" method="GET" target="_blank">
                                            <input type="hidden" name="constancia_id" value="{{ $constancia->id }}">
                                            <flux:button
                                                variant="primary"
                                                type="submit"
                                                class="cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white focus-visible:ring-2 focus-visible:ring-indigo-500"
                                                title="Descargar PDF"
                                                aria-label="Descargar PDF"
                                            >
                                                <flux:icon.download />
                                            </flux:button>
                                        </form>

                                        <flux:button
                                            variant="primary"
                                            class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white focus-visible:ring-2 focus-visible:ring-amber-500"
                                            @click="Livewire.dispatch('abrirConstancia', { id: {{ $constancia->id }} })"
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
                                            class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white focus-visible:ring-2 focus-visible:ring-rose-500"
                                            @click="destroyConstancia({{ $constancia->id }})"
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
                    @endif
                </div>
            </div>

            <!-- Paginación -->
            <div class="mt-5">
                {{ $constancias->links() }}
            </div>
        </div>
    </div>

    <!-- Modal editar -->
    <livewire:admin.documentacion.constancia.editar-constancia />
</div>
