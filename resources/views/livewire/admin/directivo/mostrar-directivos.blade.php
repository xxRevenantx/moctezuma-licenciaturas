<!-- listado-pro -->
<div
    x-data="{
        destroyDirectivo(id, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `El directivo ${nombre} se eliminará de forma permanente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((r) => r.isConfirmed && @this.call('eliminarDirectivo', id))
        }
    }"
    class="space-y-5"
>
    <!-- Encabezado -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Directivos</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Busca, importa, exporta, edita o elimina personal directivo.</p>
    </div>

    <!-- Contenedor listado -->
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow">
        <!-- Acabado superior -->
        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-sky-400 to-indigo-600"></div>

        <!-- Toolbar -->
        <div class="p-4 sm:p-5 lg:p-6">
            <div class="flex flex-col gap-3 lg:gap-4 sm:flex-row sm:items-center sm:justify-between">
                <!-- Buscador -->
                <div class="w-full sm:max-w-xl">
                    <label for="buscar-directivo" class="sr-only">Buscar Directivo</label>
                    <flux:input
                        id="buscar-directivo"
                        type="text"
                        wire:model.live="search"
                        placeholder="Buscar por nombre, cargo, identificador o correo…"
                        icon="magnifying-glass"
                        class="w-full"
                    />
                </div>

                <!-- Resumen + acciones -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 rounded-lg border border-gray-200 dark:border-neutral-800 px-3 py-1.5 bg-gray-50 dark:bg-neutral-800/60">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            Resultados:
                            <strong>{{ method_exists($directivos, 'total') ? $directivos->total() : $directivos->count() }}</strong>
                        </span>
                    </div>

                    @if($directivos->isNotEmpty())
                        <flux:button
                            wire:click="exportarDirectivos"
                            variant="primary"
                            class="cursor-pointer bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500"
                            title="Exportar directivos"
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

            <!-- Importar -->
            <form wire:submit.prevent="importarDirectivos" class="mt-3 sm:mt-4">
                @error('archivo')
                    <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
                @enderror

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="relative">
                        <label
                            for="archivo-import-directivos"
                            title="Seleccionar archivo"
                            class="cursor-pointer flex items-center gap-3 px-4 py-2.5 rounded-xl ring-1 ring-gray-200 dark:ring-neutral-800 bg-gray-50 dark:bg-neutral-800/60 hover:bg-gray-100 dark:hover:bg-neutral-800 transition"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H5.625A1.125 1.125 0 0 0 4.5 3.375v17.25A1.125 1.125 0 0 0 5.625 21.75h12.75A1.125 1.125 0 0 0 19.5 20.625V11.25m-6.75 6.75-3-3m3 3 3-3m-3 3v-6"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Selecciona un archivo (.xlsx, .xls, .csv)</span>
                        </label>
                        <input
                            id="archivo-import-directivos"
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            wire:model.live="archivo"
                            class="hidden"
                        >
                        <div wire:loading wire:target="archivo" class="mt-1 text-xs text-blue-600 dark:text-blue-400">Cargando archivo…</div>
                    </div>

                    <flux:button variant="primary" type="submit" class="w-full sm:w-auto cursor-pointer">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 16l-4-4h3V4h2v8h3l-4 4Z"/><path d="M5 18h14v2H5z"/></svg>
                            Importar
                        </div>
                    </flux:button>
                </div>
            </form>
        </div>

        <!-- Área de resultados -->
       <div class="px-4 pb-4 sm:px-5 sm:pb-6 lg:px-6">
  <div class="relative">

    <!-- Loader -->
    <div
      wire:loading.delay
      wire:target="search, exportarDirectivos, importarDirectivos, eliminarDirectivo, archivo"
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

    <!-- ⬇️ Contenido que se desenfoca mientras se busca/carga -->
    <div
      class="transition filter duration-200"
      wire:loading.class="blur-sm"
      wire:target="search, exportarDirectivos, importarDirectivos, eliminarDirectivo, archivo"
    >

                <!-- Tabla (desktop) -->
                <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900">
                    <div class="overflow-x-auto max-h-[65vh]">
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0 z-10 bg-gray-50/95 dark:bg-neutral-900/95 backdrop-blur text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-neutral-800">
                                <tr>
                                    <th class="px-4 py-3 text-center font-semibold">#</th>
                                    <th class="px-4 py-3 text-left font-semibold">Título</th>
                                    <th class="px-4 py-3 text-left font-semibold">Nombre completo</th>
                                    <th class="px-4 py-3 text-left font-semibold">Cargo</th>
                                    <th class="px-4 py-3 text-left font-semibold">Identificador</th>
                                    <th class="px-4 py-3 text-center font-semibold">Teléfono</th>
                                    <th class="px-4 py-3 text-left font-semibold">Correo</th>
                                    <th class="px-4 py-3 text-center font-semibold">Status</th>
                                    <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                @if($directivos->isEmpty())
                                    <tr>
                                        <td colspan="9" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                            <div class="mx-auto w-full max-w-md">
                                                <div class="rounded-2xl border border-dashed border-gray-300 dark:border-neutral-700 p-6">
                                                    <div class="mb-1 text-base font-semibold">No hay directivos disponibles</div>
                                                    <p class="text-sm">Ajusta tu búsqueda o importa un archivo.</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    @foreach($directivos as $key => $directivo)
                                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-neutral-800/50">
                                            <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $key+1 }}</td>
                                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $directivo->titulo }}</td>
                                            <td class="px-4 py-3 text-gray-900 dark:text-white">
                                                {{ $directivo->nombre }} {{ $directivo->apellido_paterno }} {{ $directivo->apellido_materno }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $directivo->cargo }}</td>
                                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $directivo->identificador }}</td>
                                            <td class="px-4 py-3 text-center text-gray-800 dark:text-gray-200">{{ $directivo->telefono }}</td>
                                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $directivo->correo }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if($directivo->status == "true")
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
                                                <div class="flex items-center justify-center gap-2">
                                                    <flux:button
                                                        variant="primary"
                                                        class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                                        @click="Livewire.dispatch('abrirDirectivo', { id: {{ $directivo->id }} })"
                                                        title="Editar"
                                                        aria-label="Editar"
                                                    >
                                                        <flux:icon.pencil />
                                                    </flux:button>

                                                    <flux:button
                                                        variant="danger"
                                                        class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white"
                                                        @click="destroyDirectivo({{ $directivo->id }}, '{{ $directivo->nombre }}')"
                                                        title="Eliminar"
                                                        aria-label="Eliminar"
                                                    >
                                                        <flux:icon.trash />
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
                    @if($directivos->isEmpty())
                        <div class="rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-6 text-center">
                            <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">No hay directivos</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Ajusta tu búsqueda o importa datos.</p>
                        </div>
                    @else
                        @foreach($directivos as $key => $d)
                            <div class="rounded-xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <span>#{{ $key+1 }}</span>
                                            <span class="font-medium">{{ $d->titulo }}</span>
                                            @if($d->status == "true")
                                                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-300/60 bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-700/50">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full border border-rose-300/60 bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-700 dark:bg-rose-900/20 dark:text-rose-300 dark:border-rose-700/50">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-1 font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $d->nombre }} {{ $d->apellido_paterno }} {{ $d->apellido_materno }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Cargo:</span> {{ $d->cargo }} ·
                                            <span class="font-medium">ID:</span> {{ $d->identificador }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $d->telefono }} · {{ $d->correo }}
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex flex-col gap-2">
                                        <flux:button
                                            variant="primary"
                                            class="cursor-pointer bg-amber-500 hover:bg-amber-600 text-white"
                                            @click="Livewire.dispatch('abrirDirectivo', { id: {{ $d->id }} })"
                                            title="Editar"
                                            aria-label="Editar"
                                        >
                                            <flux:icon.pencil />
                                        </flux:button>

                                        <flux:button
                                            variant="danger"
                                            class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white"
                                            @click="destroyDirectivo({{ $d->id }}, '{{ $d->nombre }}')"
                                            title="Eliminar"
                                            aria-label="Eliminar"
                                        >
                                            <flux:icon.trash />
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
                {{ $directivos->links() }}
            </div>
        </div>
    </div>

    <!-- Modal editar -->
    <livewire:admin.directivo.editar-directivo />
</div>
